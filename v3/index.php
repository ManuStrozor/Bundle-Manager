<?php
use \App\Morris;

require 'inc/inits.php';

$products = $db->fetchAll("SELECT id_keymanager_product, kp.id_product, name, (SELECT id_image FROM ".PREFIX_."image_shop WHERE id_product = kp.id_product AND cover = 1 AND id_shop = 1) AS id_image, (SELECT quantity FROM ".PREFIX_."stock_available WHERE id_product = kp.id_product) AS stav FROM ".PREFIX_."keymanager_product kp INNER JOIN ".PREFIX_."product_lang pl WHERE kp.id_product = pl.id_product AND active = 1 AND id_shop = 1 AND id_lang = 1 ORDER BY name ASC");

// Sync stock keymanager and prestashop
if (isset($_POST['sync']))
{
    $a = 0;
    foreach ($products as $product)
    {
        $stock = $db->fetchColumn("SELECT COUNT(id_keymanager) FROM ".PREFIX_."keymanager WHERE id_keymanager_product = {$product['id_keymanager_product']} AND id_order_detail = 0 AND active = 1");
        if ($product['stav'] != $stock)
        {
            $db->exec("UPDATE ".PREFIX_."stock_available SET quantity = $stock WHERE id_product = {$product['id_product']}");
            $a++;
        }
    }
    if ($a > 0) header('location:.');
}

$morris = new Morris($db, 30);

ob_start(); // Page content
?>

<div class="row">
    <div class="col-12 col-xl-6">
        <div id="graph" class="box-shadow mb-3"></div>
        <ul class="list-group box-shadow mb-3">
            <li class="list-group-item">
                <strong>Pages</strong>
            </li>
            <div class="row col-12" style="margin:0;padding:0">
            	<li class="list-group-item col-6">
	                <a class="nav-link notlink" href="orders.php">
	                    <i class="fas fa-shopping-bag"></i> <?= $l['Orders'] ?>
	                </a>
	            </li>
	            <li class="list-group-item col-6">
	                <a class="nav-link notlink" href="customers.php">
	                    <i class="fas fa-users"></i> <?= $l['Customers'] ?>
	                </a>
	            </li>
            </div>
            <div class="row col-12" style="margin:0;padding:0">
            	<li class="list-group-item col-4">
	                <a class="nav-link notlink" href="platforms.php">
	                    <i class="fas fa-laptop"></i> <?= $l['Platforms'] ?>
	                </a>
	            </li>
	            <li class="list-group-item col-4">
	                <a class="nav-link notlink" href="games.php">
	                    <i class="fas fa-gamepad"></i> <?= $l['Games'] ?>
	                </a>
	            </li>
	            <li class="list-group-item col-4">
	                <a class="nav-link notlink" href="keys">
	                    <i class="fas fa-key"></i> <?= $l['Keys'] ?>
	                </a>
	            </li>
            </div>
        </ul>
    </div>
    <div class="col-12 col-xl-6">
        <ul class="list-group box-shadow mb-3">
            <li class="list-group-item">
                <form method="POST">
                    <img src="/modules/keymanager/logo.png" height="40">
                    <strong><?= $l['Key Manager products'] ?></strong>
                    <small class="float-right">
                        <input type="hidden" name="sync"/>
                        <button class="btn btn-link" type="submit"><?= $l['Fix the stock'] ?></button>
                    </small>
                </form>
            </li>
            <?php foreach ($products as $product): ?>
            <?php
            $stock = $db->fetchColumn("SELECT COUNT(id_keymanager) FROM ".PREFIX_."keymanager WHERE id_keymanager_product = {$product['id_keymanager_product']} AND id_order_detail = 0 AND active = 1");
            $color = ($stock < 1) ? "#e74c3c" : "#007bff";
            ?>
            <li class="list-group-item">
                <img src="/img/tmp/product_mini_<?= $product['id_image'] ?>.jpg?time=<?= time() ?>" /> <?= $product['name'] ?>
                <small class="float-right" style="color:<?= ($stock < 1) ? "#e74c3c" : "#27ae60" ?>">
                    <?= ($stock < 1) ? $l['out of stock'] : $stock.' '.$l['in stock'] ?>
                    <?= ($stock != $product['stav']) ? '<kbd>'.$product['stav'].'</kbd>' : null ?>
                </small>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
ob_start(); // Foot scripts
?>

<script>
new Morris.Line({
    element: 'graph',
    data:   <?= $morris->getData() ?>,
    events: <?= $morris->getEvents() ?>,
    eventLineColors: ['#EAEAEA'],
    lineColors: ['#007bff'],
    xkey: 'day',
    ykeys: ['value'],
    labels: ['<?= $l['Sales'] ?>'],
    postUnits: [' <?= $l['$'] ?>'],
    xLabelFormat: function(day) {
        var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        return day.getDate() + ' ' + months[day.getMonth()];
    },
    hoverCallback: function (index, options) {
        var data = options.data[index];
        return data.format + '<br><b style="color:#007bff">' + data.value + ' <?= $l['$'] ?></b>';
    },
    hideHover: 'auto',
    eventStrokeWidth: 3,
    resize: true
});
</script>

<?php
$footScript = ob_get_clean();
ob_start(); // Head styles
?>

<link href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css" rel="stylesheet" />

<?php
$headStyle = ob_get_clean();
ob_start(); // Head scripts
?>

<script src="js/raphael-2.1.0.min.js"></script>
<script src="js/morris-0.5.1.min.js"></script>

<?php
$headScript = ob_get_clean();
$pageTitle = $l['Dashboard'];
require 'inc/default.php';
