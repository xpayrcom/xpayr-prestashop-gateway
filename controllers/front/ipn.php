<?php

class XpayrIpnModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function postProcess()
    {
        @ob_clean();

        if (!$this->module->active) {
            $this->respond(503, ['ok' => false, 'error' => 'module_inactive']);
            return;
        }

        $raw = file_get_contents('php://input');
        $data = json_decode((string) $raw, true);
        if (!is_array($data)) {
            $this->respond(400, ['ok' => false, 'error' => 'invalid_json']);
            return;
        }

        $secret = trim((string) Configuration::get('XPAYR_WEBHOOK_SECRET'));
        if ($secret !== '') {
            $headerSig = isset($_SERVER['HTTP_X_XPAYR_SIGNATURE']) ? (string) $_SERVER['HTTP_X_XPAYR_SIGNATURE'] : '';
            if ($headerSig === '') {
                $this->respond(401, ['ok' => false, 'error' => 'missing_signature']);
                return;
            }

            $expected = hash_hmac('sha256', (string) $raw, $secret);
            if (!hash_equals($expected, $headerSig)) {
                $this->module->debug('ipn: invalid signature expected=' . $expected . ' got=' . $headerSig);
                $this->respond(401, ['ok' => false, 'error' => 'invalid_signature']);
                return;
            }
        }

        $event = strtolower((string) ($data['event'] ?? ''));
        $metadata = isset($data['metadata']) && is_array($data['metadata']) ? $data['metadata'] : [];
        $orderId = (int) ($metadata['prestashop_order_id'] ?? 0);
        if ($orderId <= 0 && isset($data['order_id'])) {
            $orderId = (int) $data['order_id'];
        }

        if ($orderId <= 0) {
            $this->respond(400, ['ok' => false, 'error' => 'missing_order_id']);
            return;
        }

        $order = new Order($orderId);
        if (!Validate::isLoadedObject($order)) {
            $this->respond(404, ['ok' => false, 'error' => 'order_not_found']);
            return;
        }

        $targetStatus = $this->resolveStatusFromEvent($event);
        if ($targetStatus > 0) {
            $history = new OrderHistory();
            $history->id_order = (int) $order->id;
            $history->changeIdOrderState($targetStatus, (int) $order->id);
        }

        $tx = (string) ($data['tx_hash'] ?? ($data['transaction_hash'] ?? ''));
        $note = 'XPayr webhook event=' . ($event ?: 'unknown');
        if ($tx !== '') {
            $note .= ' tx=' . $tx;
        }
        $this->addPrivateMessage($order, $note);

        $this->respond(200, ['ok' => true]);
    }

    private function resolveStatusFromEvent($event)
    {
        if ($event === 'payment.completed') {
            $status = (int) Configuration::get('XPAYR_STATUS_PAID');
            if ($status <= 0) {
                $status = (int) Configuration::get('PS_OS_PAYMENT');
            }
            return $status;
        }

        if ($event === 'payment.failed') {
            return (int) Configuration::get('PS_OS_ERROR');
        }

        if ($event === 'payment.expired') {
            return (int) Configuration::get('PS_OS_CANCELED');
        }

        return 0;
    }

    private function addPrivateMessage(Order $order, $message)
    {
        $msg = new Message();
        $msg->id_order = (int) $order->id;
        $msg->private = 1;
        $msg->message = pSQL($message, true);
        $msg->add();
    }

    private function respond($status, array $payload)
    {
        http_response_code((int) $status);
        header('Content-Type: application/json');
        echo json_encode($payload);
        exit;
    }
}
