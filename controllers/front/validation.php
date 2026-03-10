<?php

class XpayrValidationModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function postProcess()
    {
        if (!$this->module->active || !(bool) Configuration::get('XPAYR_LIVE_MODE')) {
            Tools::redirect($this->context->link->getPageLink('order', true));
            return;
        }

        $cart = $this->context->cart;
        if (!Validate::isLoadedObject($cart) || (int) $cart->id_customer <= 0) {
            Tools::redirect($this->context->link->getPageLink('order', true));
            return;
        }

        $customer = new Customer((int) $cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect($this->context->link->getPageLink('order', true));
            return;
        }

        $pendingStatus = (int) Configuration::get('XPAYR_STATUS_PENDING');
        if ($pendingStatus <= 0) {
            $pendingStatus = (int) Configuration::get('PS_OS_PREPARATION');
        }

        $amount = (float) $cart->getOrderTotal(true, Cart::BOTH);

        $this->module->validateOrder(
            (int) $cart->id,
            $pendingStatus,
            $amount,
            $this->module->displayName,
            null,
            [],
            (int) $cart->id_currency,
            false,
            $customer->secure_key
        );

        $orderId = (int) $this->module->currentOrder;
        $order = new Order($orderId);
        if (!Validate::isLoadedObject($order)) {
            Tools::redirect($this->context->link->getPageLink('order', true));
            return;
        }

        $apiBase = rtrim((string) Configuration::get('XPAYR_API_BASE_URL'), '/');
        $secret = trim((string) Configuration::get('XPAYR_SECRET_KEY'));
        $network = strtolower(trim((string) Configuration::get('XPAYR_NETWORK')));
        $currency = strtoupper(trim((string) Configuration::get('XPAYR_CURRENCY')));

        if ($apiBase === '' || $secret === '' || $network === '' || $currency === '') {
            $this->module->debug('validation: missing config api/secret/network/currency');
            $this->addPrivateMessage($order, 'XPayr config missing.');
            Tools::redirect($this->context->link->getPageLink('order', true));
            return;
        }

        $successUrl = $this->context->link->getModuleLink(
            $this->module->name,
            'success',
            [
                'id_cart' => (int) $cart->id,
                'id_module' => (int) $this->module->id,
                'id_order' => (int) $orderId,
                'key' => (string) $customer->secure_key,
            ],
            true
        );

        $cancelUrl = $this->context->link->getModuleLink(
            $this->module->name,
            'cancel',
            [
                'id_order' => (int) $orderId,
            ],
            true
        );

        $orderRef = 'PS-' . $orderId;

        $ipnUrl = $this->context->link->getModuleLink(
            $this->module->name,
            'ipn',
            [],
            true
        );

        $payload = [
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => $currency,
            'network' => $network,
            'order_id' => $orderRef,
            'description' => sprintf('Order %s - %s', $order->reference, Configuration::get('PS_SHOP_NAME')),
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'ipn_callback_url' => $ipnUrl,
            'metadata' => [
                'source' => 'prestashop',
                'order_id' => $orderRef,
                'prestashop_order_id' => (string) $orderId,
                'prestashop_order_reference' => (string) $order->reference,
                'prestashop_cart_id' => (string) $cart->id,
            ],
        ];

        $response = $this->httpPostJson($apiBase . '/payments', $payload, $secret);

        if (!$response['ok']) {
            $this->module->debug('validation: create payment failed http=' . $response['http'] . ' err=' . $response['err'] . ' body=' . $response['body']);
            $this->addPrivateMessage($order, 'XPayr payment session creation failed.');
            Tools::redirect($this->context->link->getPageLink('order', true));
            return;
        }

        $json = $response['json'];
        $paymentUrl = (string) ($json['payment_url'] ?? '');
        // API response uses 'id' as the session identifier field
        $sessionId = (string) ($json['id'] ?? $json['session_id'] ?? '');
        $invoiceId = (string) ($json['invoice_id'] ?? '');

        if ($paymentUrl === '') {
            $this->module->debug('validation: missing payment_url body=' . $response['body']);
            $this->addPrivateMessage($order, 'XPayr returned empty payment URL.');
            Tools::redirect($this->context->link->getPageLink('order', true));
            return;
        }

        $log = 'XPayr session created';
        if ($sessionId !== '') {
            $log .= ' session_id=' . $sessionId;
        }
        if ($invoiceId !== '') {
            $log .= ' invoice_id=' . $invoiceId;
        }
        $this->addPrivateMessage($order, $log);

        Tools::redirect($paymentUrl);
    }

    private function httpPostJson($url, array $payload, $secret)
    {
        $body = json_encode($payload);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $secret,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $respBody = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        $json = json_decode((string) $respBody, true);
        $ok = !$err && $http >= 200 && $http < 300 && is_array($json);

        return [
            'ok' => $ok,
            'http' => $http,
            'err' => $err,
            'body' => (string) $respBody,
            'json' => is_array($json) ? $json : [],
        ];
    }

    private function addPrivateMessage(Order $order, $message)
    {
        $msg = new Message();
        $msg->id_order = (int) $order->id;
        $msg->private = 1;
        $msg->message = pSQL($message, true);
        $msg->add();
    }
}
