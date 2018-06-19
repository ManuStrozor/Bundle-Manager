<?php
use \App\HTML\Pagination;
use \App\HTML\Table;

require 'inc/inits.php';

// Requête SQL
$customers = $db->selectAll(array(
	'select' => array(
		'id_customer',
		'id_gender',
		'birthday',
		'firstname',
		'lastname',
		'email',
		'date_add',
		'(SELECT name FROM '.PREFIX_.'lang l WHERE l.id_lang = c.id_lang) AS lname',
		'(SELECT SUM(total_paid_real / conversion_rate) FROM '.PREFIX_.'orders o WHERE o.id_customer = c.id_customer AND o.id_shop IN (1) AND o.valid = 1) AS total_spent'
	),
	'from' => array(
		'table' => PREFIX_.'customer',
		'alias' => 'c'
	),
	'where' => array(
		'conditions' => array('active = 1'),
		'like' => array(
			'search' => $_GET['s'],
			'fields' => array(
				'id_customer',
				'firstname',
				'lastname',
				'email',
				'(SELECT name FROM '.PREFIX_.'lang l WHERE l.id_lang = c.id_lang)'
			)
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

// Pagination HTML
$pagination = new Pagination(array(
	'text' => $l['Displaying'].'[]/ %d '.$l['result(s)'],
	'database' => $db,
	'url' => array('s' => trim($_GET['s']), 'o' => $db->getOrder())
));


// Tableau HTML
$table = new Table(array(
	'pagination' => $pagination,
	'data' => $customers,
	'ignore' => $db->getIgnore(),
	'th' => array(
		'id_customer' 	=> 'ID',
		'lname' 		=> '<i class="fas fa-flag"></i> '.$l['Language'],
		'id_gender' 	=> '<i class="fas fa-venus-mars"></i>',
		'firstname' 	=> $l['Firstname'],
		'lastname' 		=> $l['Lastname'],
		'email' 		=> '<i class="fas fa-at"></i> '.$l['E-mail address'],
		'total_spent' 	=> $l['Sales'],
		'date_add' 		=> $l['Registration date']
	),
	'td' => array(
		'lname' => array(
			'php' => 'explode(" ", "%s")[0]'
		),
		'id_gender' => array(
			'php' => '(%d == 1) ? \'<i style="color:#1e90ff" class="fas fa-mars"></i>\' : \'<i style="color:#ff6b81" class="fas fa-venus"></i>\''
		),
		'email' => array(
			'str' => '<a href="mailto:%1$s"><i class="fas fa-envelope"></i></a> %1$s'
		),
		'total_spent' => array(
			'php' => 'round(%f, 2)',
			'str' => '%s '.$l['$']
		),
		'date_add' => array(
			'php' => 'explode(" ", "%s")[0]'
		)
	)
));

ob_start(); // Page content
?>

<?php if (!empty($customers)): ?>
	<?= $table->render() ?>
<?php else: ?>
	<?= $l['No result were found'] ?>
<?php endif; ?>

<?php
$pageContent = ob_get_clean();
$pageTitle = $l['Customers'];
require 'inc/default.php';
