<?php
use \App\Backup_Database;
use \App\HTML\Pagination;
use \App\HTML\Table;

require 'inc/inits.php';

$now = new DateTime("NOW");

// Export table
if (isset($_POST['export_table']))
{
    $backupDatabase = new Backup_Database(DB_SERV, DB_USER, DB_PASS, DB_NAME, $_POST['export_table']);
    $link = '<a href="backup/'.$backupDatabase->getBackupFile().'">'.$l['Download'].'</a>';

    $alertTitle = '<i class="fas fa-info-circle"></i>';
    $alertContent = $backupDatabase->backupTables($_POST['export_table']) ? "_L'exportation s'est bien déroulée._ ".$link : "_Une erreur est survenue !_";
}

// Rename
if (isset($_POST['rename']) && !empty($_POST['rename']))
{
	$game = $db->fetch("SELECT name FROM ".GAMES_TABLE." WHERE id = {$_POST['rename']}");
	$new = trim($_POST['new']);

	$alertTitle = '<i class="fas fa-i-cursor"></i>';

	if ($game['name'] != $new)
	{
		$db->exec("UPDATE ".GAMES_TABLE." set name = \"$new\", date_upd = '{$db->getDatenow()}' WHERE id = {$_POST['rename']}");
		$logs->new('data', $game['name'].' changed to '.$new);
		$alertContent = sprintf($l['%s has been renamed to %s at %s'], $game['name'], $new, $now->format($l['g:i a']));
	}
	else
	{
		$alertContent = $game['name']." _hasn't been modified_";
	}
}

// Requête SQL
$games = $db->selectAll(array(
	'select' => array(
		'id',
		'name',
		'(SELECT COUNT(id) FROM '.KEYS_TABLE.' WHERE game_id = g.id AND boxed = 0) AS nb_keys',
		'date_upd'
	),
	'from' => array(
		'table' => GAMES_TABLE,
		'alias' => 'g'
	),
	'where' => array(
		'like' => array(
			'search' => $_GET['s'],
			'fields' => array('name')
		)
	),
	'orderby' => array(
		'order' => $_GET['o'],
		'default' => 'date_upd DESC'
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
	'data' => $games,
	'ignore' => $db->getIgnore(),
	'th' => array(
		'id' 		=> 'ID',
		'name' 		=> $l['Game name'],
		'nb_keys' 	=> $l['Number of keys'],
		'date_upd'	=> $l['Date']
	),
	'td' => array(
		'name' => array(
			'str' => '<a href="#" title="'.$l['Rename'].'" data-target="#rename%2$d" data-toggle="modal">%s</a>'
		)
	)
));

ob_start(); // Page content
?>

<form class="mb-3" method="POST" name="exportForm">
    <input type="hidden" name="export_table" value="<?= GAMES_TABLE ?>" />
    <a href="#" title="<?= $l['Export'] ?> <?= $l['Games'] ?>" onclick="window.document.exportForm.submit();return false;"><i class="fas fa-save"></i> <?= $l['Export'] ?> <?= $l['Games'] ?></a>
</form>

<?php if (!empty($games)): ?>
	<?= $table->render() ?>
<?php else: ?>
	<?= $l['No result were found'] ?>
<?php endif; ?>

<?php foreach ($games as $game): ?>
<div class="modal fade" id="rename<?= $game['id'] ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        	<form method="POST">
	            <div class="modal-header">
	                <h4 class="modal-title" id="myModalLabel"><i class="fas fa-i-cursor"></i> <?= $l['Rename'].' <u>'.$game['name'].'</u>' ?></h4>
	                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	                    <i class="fas fa-times"></i>
	                    <span class="sr-only"><?= $l['Close'] ?></span>
	                </button>
	            </div>
	            <div class="modal-body">
	        		<input type="hidden" name="rename" value="<?= $game['id'] ?>" />
	        		<input type="text" class="form-control" name="new" placeholder="<?= $l['Type a new name'] ?>" value="<?= $game['name'] ?>" required/>
	        		<a target="_blank" href="http://store.steampowered.com/search/?term=<?= $game['name'] ?>"><?= $l['Search on'] ?> <i class="fab fa-steam-symbol"></i></a>
	            </div>
	            <div class="modal-footer">
	            	<button type="submit" class="btn btn-primary-outline"><?= $l['Rename'] ?></button>
	                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $l['Cancel'] ?></button>
	            </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php
$pageContent = ob_get_clean();
$pageTitle = $l['Games'];
$arianList = [
	$l['Dashboard'] => ['href' => './', 'icon' => '<i class="fas fa-tachometer-alt"></i>']
];
require 'inc/default.php';
