<?php
use \App\Autoloader;
use \App\Database;
use \App\Logs;

// Autoloader
require dirname(__DIR__).'/app/Autoloader.php';
Autoloader::register();

// Database
require __DIR__.'/db_config.php';
$db = new Database();

// Logs
$logs = new Logs($db, LOGS_TABLE);

// Display Errors
$display_errors = $db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_DISPLAY_ERRORS'");
ini_set('display_errors', $display_errors['value']);

$error_reporting = $db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_ERROR_REPORTING'");
if ($display_errors['value'] && !empty($error_reporting['value']))
{
	eval("error_reporting(".$error_reporting['value'].");");
}

// Session
session_start();

if (!empty($_SESSION['logged_in'])) {
    $result = $db->fetchColumn("SELECT COUNT(id_employee) FROM ".PREFIX_."employee WHERE id_employee = '{$_SESSION['id_employee']}' AND active = 1");
    if (!$result) header('Location:./logout.php');
}

if (empty($_SESSION['logged_in']) && !strpos($_SERVER['REQUEST_URI'], 'login.php')) {
    header('Location:./login.php');
}

if (!empty($_SESSION['logged_in']) && strpos($_SERVER['REQUEST_URI'], 'login.php')) {
    header('Location:./');
}

// Traductions
$lang = $db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_LANG'");
$file = dirname(__DIR__).'/lang/'.$lang['value'].'.php';
require (file_exists($file)) ? $file : dirname(__DIR__).'/lang/en.php';
