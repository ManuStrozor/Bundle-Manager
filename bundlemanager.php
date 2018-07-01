<?php
if (!defined('_PS_VERSION_')) {
  exit;
}

class BundleManager extends Module
{
	public function __construct()
	{
    	$this->name = 'bundlemanager';
    	$this->version = '3.1.3';
    	$this->confirmUninstall = $this->l('Click OK to delete Bundle Manager module.');
    	$this->need_instance = 0;
    	$this->tab = 'front_office_features';
    	$this->author = 'E.Turbet';
    	$this->bootstrap = true;
    	$this->displayName = $this->l('Bundle Manager');
    	$this->description = $this->l('Create and add bundles in Keymanager\'s database.');
    	$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

    	$this->ps17 = version_compare(_PS_VERSION_, '1.7', '>=');

    	parent::__construct();
  	}

  	public function install()
	{
        $lang = Db::getInstance()->getRow("SELECT iso_code FROM "._DB_PREFIX_."lang WHERE id_lang = ".Configuration::get('PS_LANG_DEFAULT'));

		if (!parent::install() ||
            !Configuration::updateValue('BUNDLEMANAGER_LANG', $lang['iso_code']) ||
            !Configuration::updateValue('BUNDLEMANAGER_VERSION', $this->version) ||
            !Configuration::updateValue('BUNDLEMANAGER_MAX_ROWS', 50) ||
            !Configuration::updateValue('BUNDLEMANAGER_KEYCRYPT_PATH', 'modules/keymanager/autokeycrypt.php') ||
            !Configuration::updateValue('BUNDLEMANAGER_NOTIFICATIONS_ENABLED', 0) ||
            !Configuration::updateValue('BUNDLEMANAGER_NOTIFICATIONS_SOUND', 'default.wav') ||
            !Configuration::updateValue('BUNDLEMANAGER_REFRESH_INTERVAL', 60000) || // every 1 min (60 sec = 60000 milisec)
            !Configuration::updateValue('BUNDLEMANAGER_REFRESH_DATA', 0) ||
            !Configuration::updateValue('BUNDLEMANAGER_DISPLAY_ERRORS', 0) ||
            !Configuration::updateValue('BUNDLEMANAGER_ERROR_REPORTING', 'E_ALL & ~E_NOTICE & ~E_WARNING') ||
            !Configuration::updateValue('BUNDLEMANAGER_NAME', 'Bundle Manager')) {
    		return false;
  		}

    	if (file_exists(dirname(__FILE__).'/install/install.php')) {
            $go_install = true;
            require_once(dirname(__FILE__).'/install/install.php');
        }

  		return true;
	}

	public function uninstall()
	{
		if (file_exists(dirname(__FILE__).'/install/uninstall.php')) {
            $go_uninstall = true;
            require_once(dirname(__FILE__).'/install/uninstall.php');
        }

		if (!parent::uninstall() ||
            !Configuration::deleteByName('BUNDLEMANAGER_LANG') ||
            !Configuration::deleteByName('BUNDLEMANAGER_VERSION') ||
            !Configuration::deleteByName('BUNDLEMANAGER_MAX_ROWS') ||
            !Configuration::deleteByName('BUNDLEMANAGER_KEYCRYPT_PATH') ||
            !Configuration::deleteByName('BUNDLEMANAGER_NOTIFICATIONS_ENABLED') ||
            !Configuration::deleteByName('BUNDLEMANAGER_NOTIFICATIONS_SOUND') ||
            !Configuration::deleteByName('BUNDLEMANAGER_REFRESH_INTERVAL') ||
            !Configuration::deleteByName('BUNDLEMANAGER_REFRESH_DATA') ||
            !Configuration::deleteByName('BUNDLEMANAGER_DISPLAY_ERRORS') ||
            !Configuration::deleteByName('BUNDLEMANAGER_ERROR_REPORTING') ||
            !Configuration::deleteByName('BUNDLEMANAGER_NAME')) {
    		return false;
		}

  		return true;
	}

	public function getContent()
	{
		$this->context->smarty->assign(
            array(
                'ps_lang_iso' => Configuration::get('BUNDLEMANAGER_LANG'),
                'bundle_manager_version' => $this->version
            )
        );

	    return $this->display(__FILE__, 'credits.tpl');
	}
}
