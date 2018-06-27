<?php
require 'inc/inits.php';

require 'inc/functions.php';

if (isset($_POST) && !empty($_POST))
{
	$separator = '#!#';
	$date = date("Y-m-d H:i:s");

	$isSeveral = $db->fetchColumn("SELECT COUNT(id_configuration) FROM ".PREFIX_."configuration WHERE name = 'KEYMANAGER_LINE_SEVERAL'");
	if ($isSeveral) $db->exec("UPDATE ".PREFIX_."configuration SET value = 1 WHERE name = 'KEYMANAGER_LINE_SEVERAL'");
	else $db->exec("INSERT INTO ".PREFIX_."configuration (name, value, date_add, date_upd) VALUES ('KEYMANAGER_LINE_SEVERAL', 1, '$date', '$date')");

	$isSeparator = $db->fetchColumn("SELECT COUNT(id_configuration) FROM ".PREFIX_."configuration WHERE name = 'KEYMANAGER_LINE_SEPARATOR'");
	if ($isSeparator) $db->exec("UPDATE ".PREFIX_."configuration SET value = '$separator' WHERE name = 'KEYMANAGER_LINE_SEPARATOR'");
	else $db->exec("INSERT INTO ".PREFIX_."configuration (name, value, date_add, date_upd) VALUES ('KEYMANAGER_LINE_SEPARATOR', '$separator', '$date', '$date')");

	$not_enough_games = array();
	$good_number = true;
	foreach ($_POST as $k => $pid)
	{
		if ($k != "box" && $k != "nbox")
		{
			$numKeys = $db->fetchColumn("SELECT COUNT(id) FROM ".KEYS_TABLE." WHERE game_id = $pid AND boxed = 0");
			if ($numKeys < $_POST['nbox'])
			{
				$game = $db->fetch("SELECT name FROM ".GAMES_TABLE." WHERE id = $pid");
				array_push($not_enough_games, $game['name']);
				$good_number = false;
			}
		}
	}

	$keycrypt = $db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_KEYCRYPT_PATH'");
	include '../../../'.$keycrypt['value'];

	if (defined('_AUTO_KEY_CRYPT_'))
	{
		if ($good_number)
		{
			for ($i = 0; $i < $_POST['nbox']; $i++)
			{
				$keys_phrase = '';
				foreach ($_POST as $k => $pid)
				{
					if ($k == "box")
					{
						$idProduct = $pid;

						$product = $db->fetch("SELECT name FROM ".PREFIX_."product_lang pl INNER JOIN ".PREFIX_."keymanager_product kp WHERE id_keymanager_product = $pid AND pl.id_product = kp.id_product AND id_shop = 1 AND id_lang = 1");

						$stock = $db->fetch("SELECT id_product FROM ".PREFIX_."keymanager_product WHERE id_keymanager_product = $pid");

						$keys_phrase = "Bundle: ".$product['name']."#!#";
					}
					else if ($k != "nbox")
					{
						$key = $db->fetch("SELECT id, game_key, (SELECT name FROM ".GAMES_TABLE." g WHERE g.id = game_id) AS gname, (SELECT name FROM ".PLATFORMS_TABLE." p WHERE p.id = platform_id) AS pname FROM ".KEYS_TABLE." k WHERE game_id = $pid AND boxed = 0 ORDER BY rand() LIMIT 1");

				    	$keys_phrase .= "(".$key['pname'].") ".$key['gname'].": ".$key['game_key']."#!#";

				    	$db->exec("UPDATE ".KEYS_TABLE." SET boxed = 1, date_upd = '$date' WHERE id = {$key['id']}");
					}
				}

				$db->exec("INSERT INTO ".PREFIX_."keymanager (id_keymanager_product, id_order_detail, id_shop, id_warehouse, key_value, key_image_type, key_image_width, key_image_height, secure_key, date_add, date_upd) VALUES ($idProduct, 0, 0, 0, AES_ENCRYPT(\"$keys_phrase\", '"._AUTO_KEY_CRYPT_."'), '', 0, 0, '{getMD5()}', '$date', '$date')");
			}

			$db->exec("UPDATE ".PREFIX_."stock_available SET quantity = quantity + {$_POST['nbox']} WHERE id_product = {$stock['id_product']}");

			$alertTitle = $l['OK'];
			if ($_POST['nbox'] == 1) $alertContent = sprintf($l['The box %s has been created and added in Key Manager !'], $product['name']);
			else 					$alertContent = sprintf($l['The %d boxes %s has been created and added in Key Manager !'], $_POST['nbox'], $product['name']);
		}
		else
		{
			$alertTitle = $l['Oops'];
			$alertContent = $l['Not enough keys in:'];
			foreach ($not_enough_games as $not_enough_game) $alertContent .= ' '.$not_enough_game.',';
		}
	}
	else
	{
		$alertTitle = '<i class="fas fa-exclamation-triangle"></i>';
		$alertContent = $l['Encryption is incorrectly configured!'].' <a href="settings.php">'.$l['Settings'].'</a>';
	}
}

$products = $db->fetchAll("SELECT id_keymanager_product, name FROM ".PREFIX_."keymanager_product kp INNER JOIN ".PREFIX_."product_lang pl WHERE kp.id_product = pl.id_product AND active = 1 AND id_shop = 1 AND id_lang = 1 ORDER BY name ASC");
$games = $db->fetchAll("SELECT id, name, (SELECT COUNT(*) FROM ".KEYS_TABLE." WHERE boxed = 0 AND game_id = g.id) AS nb_keys FROM ".GAMES_TABLE." g ORDER BY name ASC");

ob_start(); // Page content
?>

<form method="POST" onsubmit="return confirm('<?= $l['Are you sure ?'] ?>');">
	<div class="form-group form-inline">
		<select name="box" class="form-control col-md-8" required>
			<option value="" selected disabled><?= $l['Choose a box'] ?></option>
			<?php foreach($products as $product): ?>
				<option value="<?= $product['id_keymanager_product'] ?>"><?= $product['name'] ?></option>
			<?php endforeach; ?>
		</select>
		<input type="number" class="form-control col-md-1" style="margin:0 20px" min="1" name="nbox" value="1" required/>
		<button type="submit" class="btn btn-primary"><?= $l['Create and add'] ?></button>
	</div>
	<table class="table">
		<tbody>
		<?php foreach ($games as $game): ?>
		<tr>
			<td>
				<label class="cont">
					<kbd style="background-color:<?= nGradient("#e74c3c", "#27ae60", $game['nb_keys'], 10) ?>"><?= $game['nb_keys'] ?></kbd> <?= $game['name'] ?>
					<input type="checkbox" name="g<?= $game['id'] ?>" value="<?= $game['id'] ?>" />
					<span class="checkmark"></span>
				</label>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</form>

<?php
$pageContent = ob_get_clean();
ob_start(); // Head Style
?>

<style>
/* The container */
.cont {
    display: inline-block;
    position: relative;
    padding-left: 35px;
    margin-bottom: 12px;
    cursor: pointer;
    font-size: 14px;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

/* Hide the browser's default checkbox */
.cont input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

/* Create a custom checkbox */
.checkmark {
    position: absolute;
    top: 0;
    left: 0;
    height: 25px;
    width: 25px;
    background-color: #eee;
    color: #2196F3;
    text-align: center;
    font-size: 12px;
    padding-top: 5px;
}

/* On mouse-over, add a grey background color */
.cont:hover input ~ .checkmark {
    background-color: #ccc;
}

/* When the checkbox is checked, add a blue background */
.cont input:checked ~ .checkmark {
    background-color: #2196F3;
}

/* Create the checkmark/indicator (hidden when not checked) */
.checkmark:after {
    content: "";
    position: absolute;
    display: none;
}

/* Show the checkmark when checked */
.cont input:checked ~ .checkmark:after {
    display: block;
}

/* Style the checkmark/indicator */
.cont .checkmark:after {
    left: 9px;
    top: 5px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 3px 3px 0;
    -webkit-transform: rotate(45deg);
    -ms-transform: rotate(45deg);
    transform: rotate(45deg);
}
</style>

<?php
$headStyle = ob_get_clean();
$pageTitle = $l['Create boxes'];
$arianList = [
	$l['Dashboard'] => ['href' => './', 'icon' => '<i class="fas fa-tachometer-alt"></i>']
];
require 'inc/default.php';
