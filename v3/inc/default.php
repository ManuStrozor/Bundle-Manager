<?php

$root = '/modules/bundlemanager/v3';

$refresh_interval = $db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_REFRESH_INTERVAL'");
$notifs_enabled = $db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_NOTIFICATIONS_ENABLED'");
$notifs_sound = $db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_NOTIFICATIONS_SOUND'");

$d = date('Y-m-d');

$gains = $db->fetch("SELECT SUM(total_paid_tax_excl) as total FROM ".PREFIX_."orders WHERE date_add BETWEEN '$d' AND '$d 23:59:59' AND valid = 1");
$messages = $db->fetchColumn("SELECT COUNT(id_customer_thread) FROM ".PREFIX_."customer_thread WHERE status LIKE '%open%'");
$orders = $db->fetchColumn("SELECT COUNT(id_order) FROM ".PREFIX_."orders WHERE date_add BETWEEN '$d' AND '$d 23:59:59' AND valid = 1");

$keys_selected = (strpos($_SERVER['REQUEST_URI'], 'keys.php')) ? 'selected' : '';
$games_selected = (strpos($_SERVER['REQUEST_URI'], 'games.php')) ? 'selected' : '';
$customers_selected = (strpos($_SERVER['REQUEST_URI'], 'customers.php')) ? 'selected' : '';

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>BundleMANAGER | <?= $pageTitle ?></title>
		<link rel="icon" href="<?= $root ?>/img/favicon.ico">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no">
		<?= $headMeta ?>
		<link href="<?= $root ?>/css/bootstrap-4.0.0.min.css" rel="stylesheet">
		<link href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
		<link href="<?= $root ?>/css/dashboard.css" rel="stylesheet">
		<?= $headStyle ?>
		<script src="<?= $root ?>/js/jquery-3.3.1.min.js"></script>
		<script src="<?= $root ?>/js/bootstrap.bundle.min.js"></script>
		<?= $headScript ?>
	</head>
	<body>
		<button onclick="topFunction()" type="button" id="backToTopButton" class="btn btn-dark" title="<?= $l['Back to top'] ?>"><i class="fas fa-angle-up"></i></button>
		<audio id="notifs_audio">
			<source src="<?= $root ?>/sound/<?= $notifs_sound['value'] ?>" type="audio/x-wav">
		</audio>
		<!-- Header -->
		<nav class="navbar navbar-dark bg-primary sticky-top flex-md-nowrap p-0">
			<a class="navbar-brand col-sm-12 col-md-3 col-lg-2 mr-0 align-center" href="<?= $root ?>" title="BundleMANAGER 3.1">
				<i class="fas fa-box"></i> <b>Bundle</b>MANAGER
			</a>
            <?php if (!empty($_SESSION['logged_in'])): ?>
			<form method="GET" name="mysearch" style="display:contents" onsubmit="setTarget()">
				<div class="input-group">
					<div class="input-group-prepend">
						<select class="btn my-select" id="target">
							<option value="<?= $root ?>/keys.php" <?= $keys_selected ?>><?= $l['Keys'] ?></option>
							<option value="<?= $root ?>/games.php" <?= $games_selected ?>><?= $l['Games'] ?></option>
							<option value="<?= $root ?>/customers.php" <?= $customers_selected ?>><?= $l['Customers'] ?></option>
						</select>
					</div>
					<input class="form-control form-control-dark" type="text" name="s" placeholder="<?= $l['Search'] ?>" value="<?= trim($_GET['s']) ?>">
					<input type="hidden" name="o" value="<?= trim($_GET['o']) ?>">
				</div>
			</form>
			<!-- counters -->
			<a class="nav-link navbar-links" href="#" title="<?= $l['Recipe made today'] ?>">
				<img src="<?= $root ?>/img/icons/wallet.svg">
				<?php if ($gains['total'] > 0): ?>
					<span id="gain" class="label label-success"><?= round($gains['total'], 2) ?></span>
				<?php endif; ?>
			</a>
			<a class="nav-link navbar-links" href="<?= $root ?>/orders.php?f=<?= date('Y-m-d') ?>&t=<?= date('Y-m-d') ?>" title="<?= $l['Today\'s orders'] ?>">
	            <img src="<?= $root ?>/img/icons/bag.svg">
	            <?php if ($orders > 0): ?>
					<span id="orders" class="label label-warning"><?= $orders ?></span>
				<?php endif; ?>
	        </a>
	        <a class="nav-link navbar-links" href="#" title="<?= $l['After sales service'] ?>">
	            <img src="<?= $root ?>/img/icons/chat.svg">
	            <?php if ($messages > 0): ?>
					<span id="messages" class="label label-danger"><?= $messages ?></span>
				<?php endif; ?>
	        </a>
	        <!-- /counters -->
			<div class="dropdown">
				<button class="dropbtn text-nowrap">
					<img class="avatar" height="25" src="https://www.gravatar.com/avatar/<?= md5($_SESSION['email']) ?>.jpg" />
					<?= $_SESSION['firstname'].' '.$_SESSION['lastname'] ?>
				</button>
				<!-- Dropdown menu -->
				<div class="dropdown-content nav-item" style="min-width:180px">
					<a class="nav-link" href="<?= $root ?>/plus.php" title="<?= $l['Add keys'] ?>"><i class="fas fa-plus"></i> <?= $l['Add keys'] ?></a>
					<a class="nav-link" href="<?= $root ?>/create.php" title="<?= $l['Create boxes'] ?>"><i class="fas fa-box-open"></i> <?= $l['Create boxes'] ?></a>
					<hr class="m-0">
					<a class="nav-link" href="<?= $root ?>/history.php" title="<?= $l['History'] ?>"><i class="fas fa-clock"></i> <?= $l['History'] ?></a>
					<hr class="m-0">
					<a class="nav-link" href="<?= $root ?>/logout.php" title="<?= $l['Log out'] ?>"><i class="fas fa-power-off"></i> <?= $l['Log out'] ?></a>
				</div>
				<!-- /Dropdown menu -->
			</div>
			<a class="nav-link navbar-links" href="<?= $root ?>/settings.php" title="<?= $l['Settings'] ?>">
				<img src="<?= $root ?>/img/icons/settings.svg">
			</a>
			<?php endif; ?>
		</nav>
		<!-- /Header -->

		<div class="container-fluid">
			<div class="row">
				<!-- Navigation sidebar -->
				<nav class="col-md-3 col-lg-2 d-none d-md-block bg-light sidebar">
					<div class="sidebar-sticky">
					<?php if (!empty($_SESSION['logged_in'])): ?>
						<ul class="nav flex-column">
			            	<?php
			            	$navs = [
			            		$l['Dashboard'] => ['href' => '/', 				'icon' => 'fas fa-tachometer-alt'],
			            		$l['Orders'] 	=> ['href' => '/orders.php', 	'icon' => 'fas fa-shopping-bag'],
			            		$l['Customers'] => ['href' => '/customers.php', 'icon' => 'fas fa-users'],
			            		$l['Platforms'] => ['href' => '/platforms.php', 'icon' => 'fas fa-laptop'],
			            		$l['Games'] 	=> ['href' => '/games.php', 	'icon' => 'fas fa-gamepad'],
			            		$l['Keys'] 		=> ['href' => '/keys.php', 		'icon' => 'fas fa-key']
			            	];
			            	?>
			            	<?php foreach ($navs as $key => $nav): ?>
				            	<li class="nav-item <?= $pageTitle == $key ? 'active' : '' ?>">
									<a class="nav-link" href="<?= $root.$nav['href'] ?>">
										<i class="<?= $nav['icon'] ?>"></i> <?= $key ?>
									</a>
								</li>
			            	<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					</div>
				</nav>
				<!-- /Navigation sidebar -->

				<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
					<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-1">
						<h1 class="h2"><?= $pageTitle ?></h1>
						<span>
							<?php foreach ($arianList as $key => $arian): ?>
								<a class="notlink" href="<?= $arian['href'] ?>"><?= $key ?></a>
								<span class="dark-text m-1">/</span>
							<?php endforeach; ?>
							<?php if (!empty($arianList)): ?>
								<span class="dark-text"><?= $pageTitle ?></span>
							<?php endif; ?>
						</span>
					</div>

					<!-- Alert -->
					<?php if (!empty($alertTitle) || !empty($alertContent)): ?>
		            <div class="alert alert-<?= (!empty($alertType)) ? $alertType : 'info' ?> alert-dismissible">
		                <button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>
		                <h5><?= $alertTitle ?></h5>
		                <?= $alertContent ?>
		            </div>
		            <?php endif; ?>
		            <!-- /Alert -->

					<?= $pageContent ?>
				</main>
			</div>
		</div>

		<script src="<?= $root ?>/js/bootstrap.min.js"></script>

		<?php if (!empty($_SESSION['logged_in'])): ?>
	    <script>
	    function check(_data)
	    {
	        $.ajax({
	            url: '<?= $root ?>/inc/refresh.php',
	            type: 'GET',
	            data: "data=" + _data + "&from=<?= $d ?>&to=<?= $d ?> 23:59:59",
	            dataType: 'html',
	            success: function(response){
	                if (response.split('|')[1] == '1' && <?= $notifs_enabled['value'] ?>)
	                {
	                    var audio = document.getElementById("notifs_audio");
	                    audio.volume = 0.2;
	                    audio.play();
	                }
	                document.getElementById(_data).innerHTML = response.split('|')[0];
	            }
	        });
	    }
	    function check_all()
	    {
	        check("gain");
	        check("orders");
	        check("messages");
	    }
	    setInterval(function(){check_all();}, <?= $refresh_interval['value'] ?>);
	    </script>
	    <?php endif; ?>

	    <?= $footScript ?>
	    <script>
	    // When the user scrolls down 20px from the top of the document, show the button
	    window.onscroll = function() {scrollFunction()};

	    function scrollFunction() {
	        if (document.body.scrollTop > 1500 || document.documentElement.scrollTop > 1500) {
	            document.getElementById("backToTopButton").style.display = "block";
	        } else {
	            document.getElementById("backToTopButton").style.display = "none";
	        }
	    }

	    // When the user clicks on the button, scroll to the top of the document
	    function topFunction() {
	        scrollTo(document.body, -40, 500);
	        scrollTo(document.documentElement, -40, 500);
	    }

	    function scrollTo(element, to, duration) {
	        var start = element.scrollTop,
	            change = to - start,
	            currentTime = 0,
	            increment = 20;
	            
	        var animateScroll = function(){        
	            currentTime += increment;
	            var val = Math.easeInOutQuad(currentTime, start, change, duration);
	            element.scrollTop = val;
	            if(currentTime < duration) {
	                setTimeout(animateScroll, increment);
	            }
	        };
	        animateScroll();
	    }

	    Math.easeInOutQuad = function (t, b, c, d) {
	      t /= d/2;
	        if (t < 1) return c/2*t*t + b;
	        t--;
	        return -c/2 * (t*(t-2) - 1) + b;
	    };
	    </script>
	    <script src="<?= $root ?>/js/bootstrap-4.0.0.min.js"></script>
	    <script src="<?= $root ?>/js/scripts.js"></script>
	</body>
</html>
