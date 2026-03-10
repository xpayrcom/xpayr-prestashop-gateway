<?php

class XpayrSuccessModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function postProcess()
    {
        $idCart = (int) Tools::getValue('id_cart');
        $idModule = (int) Tools::getValue('id_module');
        $idOrder = (int) Tools::getValue('id_order');
        $key = (string) Tools::getValue('key');

        if ($idCart > 0 && $idModule > 0 && $idOrder > 0 && $key !== '') {
            Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $idCart . '&id_module=' . $idModule . '&id_order=' . $idOrder . '&key=' . urlencode($key));
            return;
        }

        Tools::redirect($this->context->link->getPageLink('history', true));
    }
}
