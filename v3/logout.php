<?php
require 'inc/inits.php';

if (isset($_COOKIE['bundlemanager_auth']))
{
	setcookie('bundlemanager_auth', '', 1);
}
session_destroy();

$logs->new('session', 'logged out');

header('Location:./login.php');
exit();
