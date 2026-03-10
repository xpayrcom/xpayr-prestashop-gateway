<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Xpayr extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'xpayr';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'XPayr';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('XPayr Gateway');
        $this->description = $this->l('Accept crypto payments via XPayr checkout.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall XPayr Gateway?');
        $this->ps_versions_compliancy = ['min' => '1.6.0.0', 'max' => '8.99.99'];
    }

    public function install()
    {
        if (!extension_loaded('curl')) {
            $this->_errors[] = $this->l('cURL extension must be enabled.');
            return false;
        }

        $ok = parent::install()
            && $this->registerHook('paymentOptions')
            && $this->registerHook('displayPaymentReturn')
            && $this->registerHook('paymentOptions')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayBackOfficeHeader');

        if (!$ok) {
            return false;
        }

        Configuration::updateValue('XPAYR_LIVE_MODE', false);
        Configuration::updateValue('XPAYR_TITLE', 'Pay with Crypto (XPayr)');
        Configuration::updateValue('XPAYR_DESC', 'Secure on-chain checkout via XPayr');
        Configuration::updateValue('XPAYR_API_BASE_URL', 'https://xpayr.com/api/v1');
        Configuration::updateValue('XPAYR_SECRET_KEY', '');
        Configuration::updateValue('XPAYR_NETWORK', 'bsc-testnet');
        Configuration::updateValue('XPAYR_CURRENCY', 'USDC');
        Configuration::updateValue('XPAYR_WEBHOOK_SECRET', '');
        Configuration::updateValue('XPAYR_WEBHOOK_AUTO_SYNC', 1);
        Configuration::updateValue('XPAYR_STATUS_PENDING', (int) Configuration::get('PS_OS_PREPARATION'));
        Configuration::updateValue('XPAYR_STATUS_PAID', (int) Configuration::get('PS_OS_PAYMENT'));
        Configuration::updateValue('XPAYR_DEBUG', 0);

        return true;
    }

    public function uninstall()
    {
        $keys = [
            'XPAYR_LIVE_MODE',
            'XPAYR_TITLE',
            'XPAYR_DESC',
            'XPAYR_API_BASE_URL',
            'XPAYR_SECRET_KEY',
            'XPAYR_NETWORK',
            'XPAYR_CURRENCY',
            'XPAYR_WEBHOOK_SECRET',
            'XPAYR_WEBHOOK_AUTO_SYNC',
            'XPAYR_STATUS_PENDING',
            'XPAYR_STATUS_PAID',
            'XPAYR_DEBUG',
        ];

        foreach ($keys as $key) {
            Configuration::deleteByName($key);
        }

        return parent::uninstall();
    }

    public function getContent()
    {
        $out = '';
        if (Tools::isSubmit('submitXpayrModule')) {
            $ok = $this->postProcess();
            if ($ok) {
                $out .= $this->displayConfirmation($this->l('Settings updated successfully.'));
            } else {
                $out .= $this->displayError($this->l('Settings saved but webhook auto-sync failed. Check API key/base URL.'));
            }
        }

        return $out . $this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = (int) $this->context->language->id;
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitXpayrModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => (int) $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('XPayr Gateway Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable module'),
                        'name' => 'XPAYR_LIVE_MODE',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'active_on', 'value' => true, 'label' => $this->l('Enabled')],
                            ['id' => 'active_off', 'value' => false, 'label' => $this->l('Disabled')],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'name' => 'XPAYR_TITLE',
                        'label' => $this->l('Checkout title'),
                        'desc' => $this->l('Shown on checkout payment option.'),
                    ],
                    [
                        'type' => 'text',
                        'name' => 'XPAYR_DESC',
                        'label' => $this->l('Checkout description'),
                    ],
                    [
                        'type' => 'text',
                        'name' => 'XPAYR_API_BASE_URL',
                        'label' => $this->l('API Base URL'),
                        'desc' => $this->l('Example: https://xpayr.com/api/v1'),
                    ],
                    [
                        'type' => 'text',
                        'name' => 'XPAYR_SECRET_KEY',
                        'label' => $this->l('Secret API Key'),
                        'desc' => $this->l('Use sk_test_... or sk_live_...'),
                    ],
                    [
                        'type' => 'select',
                        'name' => 'XPAYR_NETWORK',
                        'label' => $this->l('Network'),
                        'options' => [
                            'query' => $this->getNetworkOptions(),
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'name' => 'XPAYR_CURRENCY',
                        'label' => $this->l('Currency'),
                        'options' => [
                            'query' => $this->getCurrencyOptions(),
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Auto-sync webhook on save'),
                        'name' => 'XPAYR_WEBHOOK_AUTO_SYNC',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'wh_on', 'value' => true, 'label' => $this->l('Enabled')],
                            ['id' => 'wh_off', 'value' => false, 'label' => $this->l('Disabled')],
                        ],
                        'desc' => $this->l('Registers webhook URL and stores returned secret automatically.'),
                    ],
                    [
                        'type' => 'text',
                        'name' => 'XPAYR_WEBHOOK_SECRET',
                        'label' => $this->l('Webhook Secret'),
                        'desc' => $this->l('Used to verify X-XPayr-Signature header.'),
                    ],
                    [
                        'type' => 'select',
                        'name' => 'XPAYR_STATUS_PENDING',
                        'label' => $this->l('Pending order status'),
                        'options' => [
                            'query' => $this->getOrderStatuses(),
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'name' => 'XPAYR_STATUS_PAID',
                        'label' => $this->l('Paid order status'),
                        'options' => [
                            'query' => $this->getOrderStatuses(),
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Debug log'),
                        'name' => 'XPAYR_DEBUG',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'debug_on', 'value' => true, 'label' => $this->l('Enabled')],
                            ['id' => 'debug_off', 'value' => false, 'label' => $this->l('Disabled')],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    protected function getConfigFormValues()
    {
        return [
            'XPAYR_LIVE_MODE' => (bool) Configuration::get('XPAYR_LIVE_MODE', false),
            'XPAYR_TITLE' => (string) Configuration::get('XPAYR_TITLE', 'Pay with Crypto (XPayr)'),
            'XPAYR_DESC' => (string) Configuration::get('XPAYR_DESC', 'Secure on-chain checkout via XPayr'),
            'XPAYR_API_BASE_URL' => (string) Configuration::get('XPAYR_API_BASE_URL', 'https://xpayr.com/api/v1'),
            'XPAYR_SECRET_KEY' => (string) Configuration::get('XPAYR_SECRET_KEY', ''),
            'XPAYR_NETWORK' => (string) Configuration::get('XPAYR_NETWORK', 'bsc-testnet'),
            'XPAYR_CURRENCY' => (string) Configuration::get('XPAYR_CURRENCY', 'USDC'),
            'XPAYR_WEBHOOK_SECRET' => (string) Configuration::get('XPAYR_WEBHOOK_SECRET', ''),
            'XPAYR_WEBHOOK_AUTO_SYNC' => (bool) Configuration::get('XPAYR_WEBHOOK_AUTO_SYNC', 1),
            'XPAYR_STATUS_PENDING' => (int) Configuration::get('XPAYR_STATUS_PENDING', (int) Configuration::get('PS_OS_PREPARATION')),
            'XPAYR_STATUS_PAID' => (int) Configuration::get('XPAYR_STATUS_PAID', (int) Configuration::get('PS_OS_PAYMENT')),
            'XPAYR_DEBUG' => (bool) Configuration::get('XPAYR_DEBUG', 0),
        ];
    }

    protected function postProcess()
    {
        $keys = array_keys($this->getConfigFormValues());
        foreach ($keys as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

        if ((bool) Configuration::get('XPAYR_WEBHOOK_AUTO_SYNC')) {
            return $this->syncWebhookSecret();
        }

        return true;
    }

    public function hookHeader()
    {
        $this->context->controller->addCSS($this->_path . 'views/css/front.css');
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') === $this->name) {
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
        }
    }

    public function hookPayment($params)
    {
        if (!$this->active || !Configuration::get('XPAYR_LIVE_MODE')) {
            return false;
        }

        $this->smarty->assign([
            'module_dir' => $this->_path,
            'payment_title' => (string) Configuration::get('XPAYR_TITLE'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active || !Configuration::get('XPAYR_LIVE_MODE')) {
            return null;
        }

        if (empty($params['cart']) || !$this->checkCurrency($params['cart'])) {
            return null;
        }

        $option = new PaymentOption();
        $option->setCallToActionText((string) Configuration::get('XPAYR_TITLE'))
            ->setAdditionalInformation('<p>' . pSQL((string) Configuration::get('XPAYR_DESC')) . '</p>')
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true));

        $logoPath = _PS_MODULE_DIR_ . $this->name . '/logo_v2.png';
        if (file_exists($logoPath)) {
            $option->setLogo($this->_path . 'logo_v2.png');
        }

        return [$option];
    }

    public function hookDisplayPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        $order = isset($params['order']) ? $params['order'] : (isset($params['objOrder']) ? $params['objOrder'] : null);
        if (!Validate::isLoadedObject($order)) {
            return;
        }

        $currentState = $order->getCurrentOrderState();
        $status = ($currentState && $currentState->id == (int) Configuration::get('PS_OS_ERROR')) ? 'failed' : 'ok';

        $currency = new Currency((int) $order->id_currency);
        $this->smarty->assign([
            'status' => $status,
            'id_order' => $order->id,
            'reference' => $order->reference,
            'total' => Tools::displayPrice($order->total_paid, $currency, false),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }

    /**
     * Legacy hook alias for PrestaShop < 1.7.7
     */
    public function hookPaymentReturn($params)
    {
        return $this->hookDisplayPaymentReturn($params);
    }

    public function checkCurrency($cart)
    {
        return Validate::isLoadedObject(new Currency((int) $cart->id_currency));
    }

    public function getApiBaseUrl()
    {
        return rtrim((string) Configuration::get('XPAYR_API_BASE_URL'), '/');
    }

    public function getSecretKey()
    {
        return trim((string) Configuration::get('XPAYR_SECRET_KEY'));
    }

    public function buildApiHeaders()
    {
        return [
            'Authorization: Bearer ' . $this->getSecretKey(),
            'Content-Type: application/json',
            'Accept: application/json',
        ];
    }

    public function fetchNetworkCatalog()
    {
        $apiBase = $this->getApiBaseUrl();
        $secret = $this->getSecretKey();
        if ($apiBase === '' || $secret === '') {
            return [];
        }

        $ch = curl_init($apiBase . '/me/networks');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $this->buildApiHeaders(),
            CURLOPT_TIMEOUT => 20,
        ]);
        $body = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err || $http < 200 || $http >= 300) {
            $this->debug('fetchNetworkCatalog failed: ' . $err . ' HTTP=' . $http);
            return [];
        }

        $json = json_decode((string) $body, true);
        if (!is_array($json) || !isset($json['data']) || !is_array($json['data'])) {
            return [];
        }

        return $json['data'];
    }

    public function getNetworkOptions()
    {
        $rows = $this->fetchNetworkCatalog();
        $opts = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            // API response uses 'network_key' as the network identifier field
            $key = trim((string) ($row['network_key'] ?? $row['network'] ?? ''));
            if ($key === '') {
                continue;
            }
            $name = trim((string) ($row['network_name'] ?? $key));
            $suffix = !empty($row['is_testnet']) ? ' (Testnet)' : '';
            $opts[] = ['id' => $key, 'name' => $name . $suffix];
        }

        if (!$opts) {
            $opts = [
                ['id' => 'bsc-testnet', 'name' => 'BSC Testnet (Testnet)'],
                ['id' => 'base-sepolia', 'name' => 'Base Sepolia (Testnet)'],
                ['id' => 'polygon-amoy', 'name' => 'Polygon Amoy (Testnet)'],
            ];
        }

        return $opts;
    }

    public function getCurrencyOptions()
    {
        $rows = $this->fetchNetworkCatalog();
        $symbols = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            // Currencies are nested inside each network row under the 'currencies' array
            $currencies = isset($row['currencies']) && is_array($row['currencies'])
                ? $row['currencies']
                : [];
            foreach ($currencies as $currencyEntry) {
                if (!is_array($currencyEntry)) {
                    continue;
                }
                $symbol = strtoupper(trim((string) ($currencyEntry['symbol'] ?? '')));
                if ($symbol !== '') {
                    $symbols[$symbol] = $symbol;
                }
            }
        }
        ksort($symbols);

        $opts = [];
        foreach ($symbols as $symbol) {
            $opts[] = ['id' => $symbol, 'name' => $symbol];
        }

        if (!$opts) {
            $opts = [
                ['id' => 'USDC', 'name' => 'USDC'],
                ['id' => 'USDT', 'name' => 'USDT'],
            ];
        }

        return $opts;
    }

    public function syncWebhookSecret()
    {
        $apiBase = $this->getApiBaseUrl();
        $secret = $this->getSecretKey();
        if ($apiBase === '' || $secret === '') {
            return false;
        }

        $callback = rtrim(Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__, '/') . '/module/' . $this->name . '/ipn';
        $payload = json_encode(['url' => $callback]);

        $ch = curl_init($apiBase . '/webhooks');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $this->buildApiHeaders(),
            CURLOPT_TIMEOUT => 20,
        ]);
        $body = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err || $http < 200 || $http >= 300) {
            $this->debug('syncWebhookSecret failed: ' . $err . ' HTTP=' . $http . ' body=' . $body);
            return false;
        }

        $json = json_decode((string) $body, true);
        $webhookSecret = is_array($json) ? (string) ($json['secret'] ?? '') : '';
        if ($webhookSecret === '') {
            $this->debug('syncWebhookSecret: missing secret in response body=' . $body);
            return false;
        }

        Configuration::updateValue('XPAYR_WEBHOOK_SECRET', $webhookSecret);
        return true;
    }

    public function getOrderStatuses()
    {
        $langId = (int) $this->context->language->id;
        $statuses = OrderState::getOrderStates($langId);
        $opts = [];
        foreach ($statuses as $state) {
            $opts[] = [
                'id' => (int) $state['id_order_state'],
                'name' => (string) $state['name'],
            ];
        }
        return $opts;
    }

    public function debug($message)
    {
        if (!(bool) Configuration::get('XPAYR_DEBUG')) {
            return;
        }

        PrestaShopLogger::addLog('[XPAYR] ' . $message, 1);
    }
}
