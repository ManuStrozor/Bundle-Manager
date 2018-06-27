<?php
use \App\HTML\Pagination;
use \App\HTML\Table;

require 'inc/inits.php';

// Default period
$f = date(date('Y-m')."-01");
$t = date('Y-m-d');
extract($_GET);

// Requête SQL
$orders = $db->selectAll(array(
	'select' => array(
		'id_order',
		'reference',
		'payment',
		'total_paid_tax_excl',
		'(SELECT iso_code FROM '.PREFIX_.'currency cy WHERE cy.id_currency = o.id_currency) AS currency',
		'date_add',
		'(SELECT firstname FROM '.PREFIX_.'customer c WHERE c.id_customer = o.id_customer) AS firstname',
		'(SELECT lastname FROM '.PREFIX_.'customer c WHERE c.id_customer = o.id_customer) AS lastname'
	),
	'from' => array(
		'table' => PREFIX_.'orders',
		'alias' => 'o'
	),
	'where' => array(
		'conditions' => array(
			"date_add BETWEEN '$f' AND '$t 23:59:59'",
			'valid = 1'
		)
	),
	'orderby' => array(
		'order' => $_GET['o'],
		'default' => 'date_add DESC'
	),
	'limit' => array(
		'page' => $_GET['p'],
		'maxrows' => $_GET['m']
	)
));

// Data for caption (top-left of table)
$total_ht = $db->fetch("SELECT SUM(total_paid_tax_excl / (SELECT conversion_rate FROM ".PREFIX_."currency cy WHERE cy.id_currency = o.id_currency)) AS sum FROM ".PREFIX_."orders o WHERE date_add BETWEEN '$f' AND '$t' AND valid = 1");
$total_ttc = $db->fetch("SELECT SUM(total_paid_real / (SELECT conversion_rate FROM ".PREFIX_."currency cy WHERE cy.id_currency = o.id_currency)) AS sum FROM ".PREFIX_."orders o WHERE date_add BETWEEN '$f' AND '$t' AND valid = 1");

// Pagination HTML
$pagination = new Pagination(array(
	'text' => $l['Displaying'].'[]/ %d '.$l['result(s)'],
	'database' => $db,
	'url' => array('f' => $f, 't' => $t, 'o' => $db->getOrder())
));

// Tableau HTML
$table = new Table(array(
	'pagination' => $pagination,
	'data' => $orders,
	'ignore' => $db->getIgnore(),
	'th' => array(
		'id_order' => 'ID',
		'reference' => '<i class="fas fa-barcode"></i> '.$l['Order Ref'],
		'firstname' => $l['Firstname'],
		'lastname' => $l['Lastname'],
		'payment' => '<i class="fas fa-credit-card"></i> '.$l['Payment method'],
		'total_paid_tax_excl' => $l['Price excl. tax'],
		'currency' => $l['Currency'],
		'date_add' => $l['Date']
	),
	'td' => array(
		'total_paid_tax_excl' => array(
			'php' => 'round(%f, 2)'
		)
	)
));

ob_start(); // Page content
?>

<form class="form-inline" method="GET">
	<h4><i class="far fa-calendar-alt"></i> <?= $l['Period'] ?></h4>
	<div class="col-auto">
		<div class="input-group mb-2">
			<div class="input-group-prepend">
					<div class="input-group-text"><?= $l['From'] ?></div>
			</div>
			<input type="date" class="form-control" name="f" value="<?= date_format(date_create($f), 'Y-m-d') ?>" required>
		</div>
	</div>
	<div class="col-auto">
		<div class="input-group mb-2">
			<div class="input-group-prepend">
					<div class="input-group-text"><?= $l['To'] ?></div>
			</div>
			<input type="date" class="form-control" name="t" value="<?= date_format(date_create($t), 'Y-m-d') ?>" required>
		</div>
	</div>
	<input type="hidden" name="o" value="<?= $db->getOrder() ?>">
	<button type="submit" class="btn btn-primary mb-2"><i class="far fa-calendar-check"></i> <?= $l['Validate the period'] ?></button>
</form>

<?php if (!empty($orders)): ?>
	<caption>
		<?php $tva = $total_ttc['sum']-$total_ht['sum']; ?>
		<strong><?= $l['Total excl. tax'] ?>:</strong> <?= round($total_ht['sum'], 2) ?> <?= $l['$'] ?> <?php if ($tva > 0) echo "(".round($total_ttc['sum'], 2)." - ".round($tva, 2).")"; ?>
		<i class="fas fa-shopping-basket"></i> <?= round(($total_ht['sum'] / $db->getTotal()), 2) ?> <?= $l['$'] ?>
	</caption>
	<br>
	<?= $table->render() ?>
<?php else: ?>
	<?= $l['No result were found'] ?>
<?php endif; ?>

<?php
$pageContent = ob_get_clean();
$pageTitle = $l['Orders'];
$arianList = [
	$l['Dashboard'] => ['href' => './', 'icon' => '<i class="fas fa-tachometer-alt"></i>']
];
require 'inc/default.php';
