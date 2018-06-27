<?php
use \DateTime;
use \App\Browser;

require 'inc/inits.php';

if (isset($_POST) && !empty($_POST['from']) && !empty($_POST['to']))
{
	$f = $_POST['from'];
	$t = $_POST['to'];
}
else
{
	$f = date('Y-m-d');
	$t = date('Y-m-d 23:59:59');
}

$res = $db->fetchAll("SELECT * FROM ".LOGS_TABLE." WHERE date_upd BETWEEN '$f' AND '$t' ORDER BY date_upd DESC, id DESC");

$bro = new Browser();

ob_start(); // Page content
?>

<div style="margin-bottom:20px;text-align:center" class="row">
	<div class="col-md-3">
		<form method="POST">
			<input type="hidden" name="from" value="<?= date('Y-m-d') ?>" />
			<input type="hidden" name="to" value="<?= date('Y-m-d 23:59:59') ?>" />
			<button type="submit" class="btn btn-outline-primary"><?= $l['Today'] ?></button>
		</form>
	</div>
	<div class="col-md-3">
		<form method="POST">
			<input type="hidden" name="from" value="<?= date('Y-m-d', time()-60*60*24) ?>" />
			<input type="hidden" name="to" value="<?= date('Y-m-d 23:59:59', time()-60*60*24) ?>" />
			<button type="submit" class="btn btn-outline-primary"><?= $l['Yesterday'] ?></button>
		</form>
	</div>
	<div class="col-md-3">
		<form method="POST">
			<input type="hidden" name="from" value="<?= date('Y-m-d', time()-60*60*24*7) ?>" />
			<input type="hidden" name="to" value="<?= date('Y-m-d 23:59:59') ?>" />
			<button type="submit" class="btn btn-outline-primary"><?= $l['Last 7 days'] ?></button>
		</form>
	</div>
	<div class="col-md-3">
		<form method="POST">
			<input type="hidden" name="from" value="<?= date('Y-m-01') ?>" />
			<input type="hidden" name="to" value="<?= date('Y-m-d 23:59:59') ?>" />
			<button type="submit" class="btn btn-outline-primary"><?= $l['This month'] ?></button>
		</form>
	</div>
</div>

<table class="table">
	<tbody>
		<?php foreach ($res as $key => $item):

			$bro->setUserAgent($item['user_agent']);
			$ua = $bro->getBrowser()." ".$l['on']." ".$bro->getPlatform();

			$date = new Datetime("now");
			$before = new Datetime($item['date_upd']);
			$diff = $date->diff($before);

			$dago = $diff->format("%d");
			$hago = $diff->format("%h");
			$iago = $diff->format("%i");

			if ($dago > 0)
				$ago = date("d M, ".$l['g:i a'], strtotime($item['date_upd']));
			else
			{
				if ($hago > 0)
					$ago = $hago." h";
				else
				{
					if ($iago > 0)
						$ago = $iago." _min_";
					else
						$ago = $l['just now'];
				}
			}
		?>
		<tr>
			<td>
				<div class="d-flex w-100 justify-content-between">
					<h5 class="mb-1"><?= $item['title'] ?></h5>
					<small class="text-muted aslink" title="<?= $item['date_upd'] ?>"><?= $ago ?></small>
				</div>
				<p class="mb-1">
					<?= preg_replace("/([A-Za-z0-9]{4,5}(-[A-Za-z0-9]{4,6}){2,4})/", "<a class='preg-key' href='keys.php?s=$1'>$1</a>", $item['description']) ?>
				</p>
				<small class="text-muted">
					<a href="http://ip-api.com/#<?= $item['ip_address'] ?>" target="_blank" style="font-size:16px"><i class="fas fa-map-marker-alt"></i></a> <?= $item['ip_address'] ?>
					<span class="aslink" title="<?= $item['user_agent'] ?>">(<?= $ua ?>)</span>
				</small>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php
$pageContent = ob_get_clean();
ob_start(); // Head style
?>

<style>
	.preg-key {
		border: 1px solid lightgray;
		border-radius: 3px;
		padding: 2px;
		color: gray;
	}
</style>

<?php
$headStyle = ob_get_clean();
$pageTitle = $l['History'];
$arianList = array(
	$l['Dashboard'] => array('href' => './', 'icon' => '<i class="fas fa-tachometer-alt"></i>'),
	$l['Settings'] => array('href' => 'settings.php')
);
require 'inc/default.php';
