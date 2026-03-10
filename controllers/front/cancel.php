<?php

class XpayrCancelModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function postProcess()
    {
        Tools::redirect($this->context->link->getPageLink('order', true));
    }
}
