<?php
if (!isset($go_install)) {
    exit;
}

/* Get languages */
$languages = Db::getInstance()->executeS('SELECT `id_lang`, `iso_code` FROM `'._DB_PREFIX_.'lang`');

/* Database configuration file */
$file = fopen(_PS_MODULE_DIR_.$this->name."/v3/inc/db_config.php", "w");
$content = <<<EOT
<?php

define('DB_SERV', '%s');
define('DB_NAME', '%s');
define('DB_USER', '%s');
define('DB_PASS', '%s');
define('PREFIX_', '%s');

define('KEYS_TABLE',        PREFIX_.'bundlemanager_keys');
define('GAMES_TABLE',       PREFIX_.'bundlemanager_games');
define('PLATFORMS_TABLE',   PREFIX_.'bundlemanager_platforms');
define('LOGS_TABLE',        PREFIX_.'bundlemanager_logs');

EOT;
fwrite($file, sprintf($content, _DB_SERVER_, _DB_NAME_, _DB_USER_, _DB_PASSWD_, _DB_PREFIX_));
fclose($file);

/* Database init tables */
$sqls = array(
    'CREATE TABLE IF NOT EXISTS `PREFIX_bundlemanager_keys` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`game_id` int(11) NOT NULL,
			`game_key` varchar(255) NOT NULL,
			`platform_id` int(11) NOT NULL,
			`boxed` tinyint(1) NOT NULL,
			`date_upd` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1',
	'CREATE TABLE IF NOT EXISTS `PREFIX_bundlemanager_games` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(255) NOT NULL,
            `date_upd` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1',
	'CREATE TABLE IF NOT EXISTS `PREFIX_bundlemanager_platforms` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
	) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1',
    'CREATE TABLE IF NOT EXISTS `PREFIX_bundlemanager_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `description` varchar(255) NOT NULL,
            `user_agent` varchar(255) NOT NULL,
            `ip_address` varchar(255) NOT NULL,
            `date_upd` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1'
);

/* Create tables if not exist */
foreach ($sqls as $sql) {
    $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
    if (Validate::isString($sql)) {
        Db::getInstance()->execute($sql);
    }
}


/* Tab translations */
$tabs_translation = array(
    'AdminBundleManager' =>  array(
        'en' => 'Bundle Manager',
        'fr' => 'Bundle Manager'
    ),
    'AdminBundleManagerKeys' => array(
        'en' => 'Keys',
        'fr' => 'Clés'
    )
);

/* Install Tab */
$tabs = array();
foreach ($languages as $l) {
    foreach ($tabs_translation as $n => $t) {
        $tabs[$n][(int)$l['id_lang']] = (isset($t[$l['iso_code']]) ? $t[$l['iso_code']] : $t['en']);
    }
}

$id_parent = 0;
if ($this->ps17) {
    $id_parent = (int)Db::getInstance()->getValue("
        SELECT id_tab
        FROM "._DB_PREFIX_."tab
        WHERE class_name = 'SELL'
    ");
}

$tab = new Tab();
$tab->name = $tabs['AdminBundleManager'];
$tab->class_name = 'AdminBundleManager';
$tab->module = 'bundlemanager';
$tab->id_parent = (int)$id_parent;
$tab->save();
$id_parent = (int)$tab->id;

/* Subtab */
unset($tabs['AdminBundleManager']); /* remove parent tab */
foreach ($tabs as $n => $t) {
    $tab = new Tab();
    $tab->name = $t;
    $tab->class_name = $n;
    $tab->module = 'bundlemanager';
    $tab->id_parent = (int)$id_parent;
    $tab->save();
}

Db::getInstance()->execute("
    UPDATE "._DB_PREFIX_."tab
    SET icon = 'inbox'
    WHERE id_tab = $id_parent
");

/* Verification file */
$file = fopen(_PS_MODULE_DIR_.$this->name."/v3/inc/inits.php", "w");
$content = <<<EOT
<?php
use \App\Autoloader;
use \App\Database;
use \App\Logs;

// Autoloader
require dirname(__DIR__).'/app/Autoloader.php';
Autoloader::register();

// Database
require __DIR__.'/db_config.php';
\$db = new Database();

// Logs
\$logs = new Logs(\$db, LOGS_TABLE);

// Display Errors
\$display_errors = \$db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_DISPLAY_ERRORS'");
ini_set('display_errors', \$display_errors['value']);

\$error_reporting = \$db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_ERROR_REPORTING'");
if (\$display_errors['value'] && !empty(\$error_reporting['value']))
{
    eval("error_reporting(".\$error_reporting['value'].");");
}

// Session
session_start();

if (!empty(\$_SESSION['logged_in'])) {
    \$result = \$db->fetchColumn("SELECT COUNT(id_employee) FROM ".PREFIX_."employee WHERE id_employee = '{\$_SESSION['id_employee']}' AND active = 1");
    if (!\$result) header('Location:./logout.php');
}

if (empty(\$_SESSION['logged_in']) && !strpos(\$_SERVER['REQUEST_URI'], 'login.php')) {
    header('Location:./login.php');
}

if (!empty(\$_SESSION['logged_in']) && strpos(\$_SERVER['REQUEST_URI'], 'login.php')) {
    header('Location:./');
}

// Traductions
\$lang = \$db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_LANG'");
\$file = dirname(__DIR__).'/lang/'.\$lang['value'].'.php';
require (file_exists(\$file)) ? \$file : dirname(__DIR__).'/lang/en.php';

EOT;
fwrite($file, $content);
fclose($file);
