<?php
require 'inc/inits.php';

if (isset($_POST) && !empty($_POST))
{
	$game_id = 0;
	$platform_id = 0;

	extract($_POST);
	$keys = explode(PHP_EOL, trim($keys));

	$alertTitle = '<i class="fas fa-check"></i>';
	$alertContent = "";

	if ($game_id == 0 && isset($newGame) && !empty($newGame))
	{
		$newGame = trim($newGame);
		$found = $db->fetch("SELECT id FROM ".GAMES_TABLE." WHERE name = \"$newGame\"");
		if (is_null($found['id']))
		{
			$db->exec("INSERT INTO ".GAMES_TABLE." (name, date_upd) VALUES (\"$newGame\", '{$db->getDatenow()}')");
			$logs->new('data', $newGame.' created and added to the games table');
			$last = $db->fetch("SELECT id FROM ".GAMES_TABLE." WHERE name = \"$newGame\"");
			$game_id = $last['id'];
			$alertContent .= "<br>".$newGame." ".$l['was created'];
		}
		else
		{
			$alertContent .= "<br>".$newGame." ".$l['already exists'];
			$game_id = $found['id'];
		}
	}

	if ($platform_id == 0 && isset($newPlatform) && !empty($newPlatform))
	{
		$newPlatform = trim($newPlatform);
		$found = $db->fetch("SELECT id FROM ".PLATFORMS_TABLE." WHERE name = \"$newPlatform\"");
		if (is_null($found['id']))
		{
			$db->exec("INSERT INTO ".PLATFORMS_TABLE." (name) VALUES (\"$newPlatform\")");
			$logs->new('data', $newPlatform.' created and added to the platforms table');
			$last = $db->fetch("SELECT id FROM ".PLATFORMS_TABLE." WHERE name = \"$newPlatform\"");
			$platform_id = $last['id'];
			$alertContent .= "<br>".$newPlatform." ".$l['was created'];
		}
		else
		{
			$alertContent .= "<br>".$newPlatform." ".$l['already exists'];
			$platform_id = $found['id'];
		}
	}

	if (isset($game_id) && $game_id != 0 && isset($platform_id) && $platform_id != 0)
	{
		$listofkeys = "";
		foreach ($keys as $key)
		{
			$key = trim($key);
			$count = $db->fetchColumn("SELECT COUNT(id) FROM ".KEYS_TABLE." WHERE game_key = '$key'");
			if (!empty($key))
			{
				if (!$count)
				{
					$db->exec("INSERT INTO ".KEYS_TABLE." (game_key, game_id, platform_id, date_upd) VALUES ('$key', $game_id, $platform_id, '{$db->getDatenow()}')");
					$listofkeys .= $key.", ";
					$alertContent .= "<br>".$key." ".$l['added'];
				}
				else
				{
					$alertContent .= "<br>".$key." ".$l['is a duplicate'];
				}
			}
		}

		if (!empty($keys))
		{
			$info = $db->fetch("SELECT name FROM ".GAMES_TABLE." WHERE id = $game_id");
			$logs->new('data', $listofkeys."added to ".$info['name']);
		}
	}
}

ob_start(); // Page content

$games = $db->fetchAll("SELECT * FROM ".GAMES_TABLE." ORDER BY name ASC");
$plats = $db->fetchAll("SELECT * FROM ".PLATFORMS_TABLE);
?>

<form method="POST">
	<div class="form-group form-inline">
		<select name="game_id" class="form-control" required>
			<option value="" selected disabled><?= $l['Choose a game'] ?></option>
			<?php foreach($games as $game): ?>
				<option value="<?= $game['id'] ?>"><?= $game['name'] ?></option>
			<?php endforeach; ?>
			<option value="0"><?= $l['Other'] ?></option>
		</select>
		<input class="form-control" type="text" name="newGame" placeholder="<?= $l['If Other'] ?>"/>
	</div>
	<small><?= $l['One key per line'] ?></small>
	<textarea name="keys" rows="10" class="form-control"></textarea>
	<br>
	<div class="form-group form-inline">
		<select name="platform_id" class="form-control" required>
			<option value="" selected disabled><?= $l['Choose a platform'] ?></option>
			<?php foreach($plats as $plat): ?>
				<option value="<?= $plat['id'] ?>"><?= $plat['name'] ?></option>
			<?php endforeach; ?>
			<option value="0"><?= $l['Other'] ?></option>
		</select>
		<input class="form-control" type="text" name="newPlatform" placeholder="<?= $l['If Other'] ?>"/>
		<button type="submit" class="btn btn-primary form-control"><?= $l['Add the keys'] ?></button>
	</div>
</form>

<?php
$pageContent = ob_get_clean();
$pageTitle = $l['Add keys'];
$arianList = [
	$l['Dashboard'] => ['href' => './', 'icon' => '<i class="fas fa-tachometer-alt"></i>']
];
require 'inc/default.php';
