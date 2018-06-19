<?php

class AdminBundleManagerKeysController extends ModuleAdminController
{
    public function __construct()
    {
        include_once(dirname(__FILE__).'/../../bundlemanager.php');

        $this->className = 'BundleManager';
        $this->bootstrap = true;
        $this->bm = new BundleManager();
        $this->controller_name = 'AdminBundleManagerKeys';
        $this->identifier = 'id';
        $this->table = 'bundlemanager_keys';

        $this->fields_list = array(
            'id' => array(
                'title' => $this->bm->l('ID', $this->controller_name),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'g_name' => array(
                'title' => $this->bm->l('Game', $this->controller_name),
                'align' => 'center'
            ),
            'p_name' => array(
                'title' => $this->bm->l('Platform', $this->controller_name),
                'align' => 'center'
            ),
            'game_key' => array(
                'title' => $this->bm->l('Key', $this->controller_name),
                'align' => 'center'
            )
        );

        parent::__construct();
    }

    public function renderList()
    {
        if (isset($this->toolbar_btn['new'])) {
            unset($this->toolbar_btn['new']);
        }

        if ((int)$this->context->cookie->profile == 1) {
            $this->addRowAction('delete');
        }

        $this->_select = 'g.`name` AS `g_name`, p.`name` AS `p_name`';
        $this->_where = 'AND a.`boxed` = 0';
        $this->_join = '
        	LEFT JOIN `'._DB_PREFIX_.'bundlemanager_platforms` p ON (p.`id` = a.`platform_id`)
        	LEFT JOIN `'._DB_PREFIX_.'bundlemanager_games` g ON (g.`id` = a.`game_id`)
        ';

        return parent::renderList();
    }

    public function postProcess()
    {
        if ((int)$this->context->cookie->profile == 1) {
            $id = (int)Tools::getValue('id');
            if ($id && Tools::getIsset('delete'.$this->table)) {
                self::deleteKey((int)$id);
                Tools::redirectAdmin($this->context->link->getAdminLink($this->controller_name));
            }
        }
        return parent::postProcess();
    }

    public function deleteKey($id)
    {
        Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.$this->table.'` WHERE `'.$this->identifier.'` = '.(int)$id);
    }
}
