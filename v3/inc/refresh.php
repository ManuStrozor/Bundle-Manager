<?php
require_once 'inits.php';

extract($_GET);
switch ($data)
{
	case 'gain':
		if (!isset($_COOKIE['bundlemanager_refresh']))
		{
			setcookie('bundlemanager_refresh', 0, time() + 7*24*3600, null, null, false, true);
		}
		
		$gains = $db->fetch("SELECT SUM(total_paid_tax_excl) as total FROM ".PREFIX_."orders WHERE date_add > '$from' AND date_add < '$to' AND valid = 1");
		$new = round($gains['total'], 2);
		
		if ($new == $_COOKIE['bundlemanager_refresh'] || $new == 0)
		{
			echo $new.'|0';
		}
		else
		{
			echo $new.'|1';
			setcookie('bundlemanager_refresh', $new, time() + 7*24*3600, null, null, false, true);
		}
		break;
	case 'messages':
		$messages = $db->fetchColumn("SELECT COUNT(id_customer_thread) FROM ".PREFIX_."customer_thread WHERE status LIKE '%open%'");
		echo $messages.'|0';
		break;
	case 'orders':
		$orders = $db->fetchColumn("SELECT COUNT(id_order) FROM ".PREFIX_."orders WHERE date_add > '$from' AND date_add < '$to' AND valid = 1");
		echo $orders.'|0';
		break;
}
