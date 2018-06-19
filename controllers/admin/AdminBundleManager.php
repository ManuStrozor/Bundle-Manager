<?php

class AdminBundleManagerController extends ModuleAdminController
{
    public function __construct()
    {
        $this->className = 'BundleManager';
        $this->bootstrap = true;

        parent::__construct();
    }

    public function initContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminBundleManagerKeys'));
        parent::initContent();
    }
}
