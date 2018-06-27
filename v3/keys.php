<?php
use \App\Backup_Database;
use \App\HTML\Pagination;
use \App\HTML\Table;

require 'inc/inits.php';

// Export table
if (isset($_POST['export_table']))
{
    $backupDatabase = new Backup_Database(DB_SERV, DB_USER, DB_PASS, DB_NAME, $_POST['export_table']);
    $link = '<a href="backup/'.$backupDatabase->getBackupFile().'">_Télécharger_ '.$backupDatabase->getBackupFile().'</a>';

    $alertTitle = '<i class="fas fa-info-circle"></i>';
    $alertContent = $backupDatabase->backupTables($_POST['export_table']) ? "_L'exportation s'est bien déroulée._ ".$link : "_Une erreur est survenue !_";
}

// Edit
if (isset($_POST['edit']) && !empty($_POST['edit']))
{
	$key = $db->fetch("SELECT game_key, game_id, platform_id FROM ".KEYS_TABLE." WHERE id = {$_POST['edit']}");

	$alertTitle = '<i class="fas fa-edit"></i>';
	$alertContent = "";

	if ($key['game_id'] != $_POST['gid'])
	{
		$db->exec("UPDATE ".KEYS_TABLE." SET game_id = {$_POST['gid']} WHERE id = {$_POST['edit']}");
		$changed = $db->fetch("SELECT (SELECT name FROM ".GAMES_TABLE." WHERE id = k.game_id) AS gname FROM ".KEYS_TABLE." k WHERE id = {$_POST['edit']}");
		$logs->new('data', 'Game of '.$key['game_key'].' changed to '.$changed['gname']);
		$alertContent .= "_Game was modified !_";
	}

	if ($key['platform_id'] != $_POST['pid'])
	{
		$db->exec("UPDATE ".KEYS_TABLE." SET platform_id = {$_POST['pid']} WHERE id = {$_POST['edit']}");
		$changed = $db->fetch("SELECT (SELECT name FROM ".PLATFORMS_TABLE." WHERE id = k.platform_id) AS pname FROM ".KEYS_TABLE." k WHERE id = {$_POST['edit']}");
		$logs->new('data', 'Platform of '.$key['game_key'].' changed to '.$changed['pname']);
		$alertContent .= "_Platform was modified !_";
	}

	if ($key['game_id'] == $_POST['gid'] && $key['platform_id'] == $_POST['pid'])
	{
		$alertContent .= "_No modifications !_";
	}
}

// IF Action delete
if (isset($_POST['del']) && !empty($_POST['del']))
{
	$key = $db->fetch("SELECT game_key, (SELECT name FROM ".GAMES_TABLE." WHERE id = k.game_id) AS gname FROM ".KEYS_TABLE." k WHERE id = {$_POST['del']}");

	$db->exec("DELETE FROM ".KEYS_TABLE." WHERE id = {$_POST['del']}");
	$logs->new('data', $key['game_key'].' ('.$key['gname'].') deleted from the keys table');
	
	$alertTitle = $l['OK'];
	$alertContent = sprintf($l['The key %s of the game %s has been removed from the database !'], $key['game_key'], $key['gname']);
}

// Requête SQL
$keys = $db->selectAll(array(
	'select' => array(
		'id',
		'game_key',
		'(SELECT name FROM '.GAMES_TABLE.' WHERE id = k.game_id) AS gname',
		'(SELECT name FROM '.PLATFORMS_TABLE.' WHERE id = k.platform_id) AS pname',
		'date_upd'
	),
	'from' => array(
		'table' => KEYS_TABLE,
		'alias' => 'k'
	),
	'where' => array(
		'conditions' => array('boxed = 0'),
		'like' => array(
			'search' => $_GET['s'],
			'fields' => array(
				'game_key',
				'(SELECT name FROM '.GAMES_TABLE.' WHERE id = k.game_id)',
				'(SELECT name FROM '.PLATFORMS_TABLE.' WHERE id = k.platform_id)'
			)
		)
	),
	'orderby' => array(
		'order' => $_GET['o'],
		'default' => 'date_upd DESC, id DESC'
	),
	'limit' => array(
		'page' => $_GET['p'],
		'maxrows' => $_GET['m']
	)
));

// Pagination HTML
$pagination = new Pagination(array(
	'text' => $l['Displaying'].'[]/ %d '.$l['result(s)'],
	'database' => $db,
	'url' => array('s' => trim($_GET['s']), 'o' => $db->getOrder())
));

// Tableau HTML
$table = new Table(array(
	'pagination' => $pagination,
	'data' => $keys,
	'ignore' => $db->getIgnore(),
	'th' => array(
		'id' => 'ID',
		'pname' => $l['Platform'],
		'gname' => $l['Game name'],
		'game_key' => $l['Key'],
		'date_upd' => $l['Date']
	),
	'td' => array(
		'game_key' => array(
			'str' => '<input class="form-control clip-key" style="background-color:white" type="text" value="%s" id="k%2$d" readonly> <a href="#" onclick=\'clipboard("k%2$d")\' title="'.$l['Copy the key'].'" data-target="#clipboard%2$d" data-toggle="modal"><i class="fas fa-clipboard"></i></a>'
		)
	)
));

$table->moreColumns(array(
	'th' => array(
		'Actions'
	),
	'td' => array(
		array(
			'str' => '<a href="#" title="'.$l['Edit'].'" data-target="#edit%2$d" data-toggle="modal"><i class="fas fa-edit"></i></a> - <a style="color:#e74c3c" href="#" onclick=\'clipboard("k%2$d")\' title="'.$l['Delete'].'" data-target="#clipboard%2$d" data-toggle="modal"><i class="fas fa-trash-alt"></i></a>'
		)
	)
));

ob_start(); // Page content
?>

<form method="POST" name="exportForm">
    <input type="hidden" name="export_table" value="<?= KEYS_TABLE ?>" />
    <a href="#" title="<?= $l['Export'] ?> <?= $l['Keys'] ?>" onclick="window.document.exportForm.submit();return false;"><i class="fas fa-save"></i> <?= $l['Export'] ?> <?= $l['Keys'] ?></a>
</form>

<?php if (!empty($keys)): ?>
	<?= $table->render() ?>
<?php else: ?>
	<?= $l['No result were found'] ?>
<?php endif; ?>

<?php foreach ($keys as $key): ?>
<div class="modal fade" id="edit<?= $key['id'] ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel"><i class="fas fa-edit"></i> <small class="text-muted"><?= $key['game_key'] ?></small></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                    <span class="sr-only"><?= $l['Close'] ?></span>
                </button>
            </div>
            <form method="POST" name="editForm">
            <div class="modal-body">
            		<div class="row">
            			<div class="col-sm-3">
            				<strong><?= $l['Game'] ?> :</strong>
            			</div>
            			<div class="col-sm-9">
            				<select name="gid" class="form-control" required>
								<?php $g = $db->fetch("SELECT id FROM ".GAMES_TABLE." WHERE name = \"{$key['gname']}\""); ?>
								<?php $games = $db->fetchAll("SELECT * FROM ".GAMES_TABLE." ORDER BY name ASC"); ?>
								<?php foreach($games as $game): ?>
									<?php if ($game['id'] == $g['id']): ?>
										<option style="color:#007bff" value="<?= $game['id'] ?>" selected><?= $game['name'] ?></option>
									<?php else: ?>
										<option value="<?= $game['id'] ?>"><?= $game['name'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
            			</div>
            		</div><br>
            		<div class="row">
            			<div class="col-sm-3">
            				<strong><?= $l['Platform'] ?> :</strong>
            			</div>
            			<div class="col-sm-9">
            				<select name="pid" class="form-control" required>
								<?php $p = $db->fetch("SELECT id FROM ".PLATFORMS_TABLE." WHERE name = \"{$key['pname']}\""); ?>
								<?php $plats = $db->fetchAll("SELECT * FROM ".PLATFORMS_TABLE." ORDER BY name ASC"); ?>
								<?php foreach($plats as $plat): ?>
									<?php if ($plat['id'] == $p['id']): ?>
										<option style="color:#007bff" value="<?= $plat['id'] ?>" selected><?= $plat['name'] ?></option>
									<?php else: ?>
										<option value="<?= $plat['id'] ?>"><?= $plat['name'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
            			</div>
            		</div>
            </div>
            <div class="modal-footer">
            	<input type="hidden" name="edit" value="<?= $key['id'] ?>">
            	<button style="background-color:#007bff;color:#ecf0f1" type="submit" class="btn btn-primary-outline"><i class="fas fa-edit"></i> <?= $l['Edit'] ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $l['Cancel'] ?></button>
            </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="clipboard<?= $key['id'] ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel"><i class="fas fa-paste"></i> <?= $l['The key was copied'] ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                    <span class="sr-only"><?= $l['Close'] ?></span>
                </button>
            </div>
            <div class="modal-body">
            	<p>
            		<strong><?= $l['Game'] ?></strong> : <?= $key['gname'] ?><br>
            		<strong><?= $l['Platform'] ?></strong> : <?= $key['pname'] ?><br>
            		<strong><?= $l['Key'] ?></strong> : <?= $key['game_key'] ?>
            	</p>
                <p><?= $l['What do you want with this key?'] ?></p>
            </div>
            <div class="modal-footer">
            	<input class="form-control verify-cp" type="text" placeholder="<?= $l['verify copy/paste'] ?>">
            	<form method="POST">
            		<input type="hidden" name="del" value="<?= $key['id'] ?>">
            		<button style="background-color:#c0392b;color:#ecf0f1" type="submit" class="btn btn-primary-outline"><i class="far fa-trash-alt"></i> <?= $l['Delete'] ?></button>
            	</form>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $l['Cancel'] ?></button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php
$pageContent = ob_get_clean();
ob_start(); // Head styles
?>

<style>
.verify-cp{
	font-size: .75rem;
	padding: .5rem .3rem;
}
.clip-key{
	display: inline-block;
	padding: 0;
	width: auto;
}
</style>

<?php
$headStyle = ob_get_clean();
ob_start(); // Head scripts
?>

<script>
function clipboard(id) {
	var copied = document.getElementById(id);
  	copied.select();
  	document.execCommand("Copy");
}
</script>

<?php
$headScript = ob_get_clean();
$pageTitle = $l['Keys'];
$arianList = array(
	$l['Dashboard'] => array('href' => './', 'icon' => '<i class="fas fa-tachometer-alt"></i>')
);
require 'inc/default.php';
