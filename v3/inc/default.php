<?php

$root = '/modules/bundlemanager/v3';

if (!strpos($_SERVER['REQUEST_URI'], 'notinstalled.php')) {

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
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Bundle Manager | <?= $pageTitle ?></title>
		<link rel="icon" href="<?= $root ?>/img/favicon.ico" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no">
		<?= $headMeta ?>
		<link href="<?= $root ?>/css/bootstrap-4.0.0.min.css" rel="stylesheet" />
		<link href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" rel="stylesheet" />
		<link href="<?= $root ?>/css/dashboard.css" rel="stylesheet" />
		<?= $headStyle ?>
		<script src="<?= $root ?>/js/jquery-3.3.1.min.js"></script>
		<script src="<?= $root ?>/js/bootstrap.bundle.min.js"></script>
		<?= $headScript ?>
	</head>
	<body>
		<button onclick="topFunction()" type="button" id="backToTopButton" class="btn btn-dark"><i class="fas fa-chevron-circle-up"></i></button>
		<audio id="notifs_audio">
			<source src="<?= $root ?>/sound/<?= $notifs_sound['value'] ?>" type="audio/x-wav" />
		</audio>

		<nav class="navbar navbar-dark bg-primary sticky-top flex-md-nowrap p-0">
			<a class="navbar-brand col-sm-3 col-md-2 mr-0" href="<?= $root ?>" title="<?= $l['Dashboard'] ?>">
				<i class="fas fa-box"></i> Bundle Manager
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
			<ul class="navbar-nav px-3">
				<li class="nav-item text-nowrap">
					<a class="nav-link" href="<?= $root ?>/logout.php" title="<?= $l['Log out'] ?>"><?= $l['Log out'] ?></a>
				</li>
			</ul>
			<?php endif; ?>
		</nav>

		<div class="container-fluid">
			<div class="row">
				<nav class="col-md-2 d-none d-md-block bg-light sidebar">
					<div class="sidebar-sticky">
						<?php if (!empty($_SESSION['logged_in'])): ?>
						<ul class="nav flex-column">
							<li class="profile-item">
			            		<img class="gravatar" src="https://www.gravatar.com/avatar/<?= md5($_SESSION['email']) ?>.jpg" /><br>
			            		<?= $_SESSION['firstname'].' '.$_SESSION['lastname'] ?>
			            	</li>
			            	<?php
			            	$navs = [
			            		[$l['Dashboard'], 'fas fa-tachometer-alt', '/'],
			            		[$l['Orders'],	  'fas fa-shopping-bag',   '/orders.php'],
			            		[$l['Customers'], 'fas fa-users', 		   '/customers.php'],
			            		[$l['Platforms'], 'fas fa-laptop', 		   '/platforms.php'],
			            		[$l['Games'], 	  'fas fa-gamepad', 	   '/games.php'],
			            		[$l['Keys'], 	  'fas fa-key', 		   '/keys.php']
			            	];
			            	?>
			            	<?php foreach ($navs as $nav): ?>
				            	<li class="nav-item">
									<a class="nav-link <?= $pageTitle == $nav[0] ? 'active' : '' ?>" href="<?= $root.$nav[2] ?>">
										<i class="fas fa-<?= $nav[1] ?>"></i> <?= $nav[0] ?>
									</a>
								</li>
			            	<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					</div>
				</nav>

				<main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
					<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
						<h1 class="h2"><?= $pageTitle ?></h1>
						<?php if (!empty($_SESSION['logged_in'])): ?>
						<div class="btn-toolbar mb-2 mb-md-0">
							<div class="btn-group mr-2">
								<a class="btn btn-sm btn-outline-secondary" href="#" title="<?= $l['Recipe made today'] ?>">
				                    <span id="gain"><?= round($gains['total'], 2) ?></span> <i class="fas fa-<?= $l['dollar'] ?>-sign"></i>
				                </a>
				                <a class="btn btn-sm btn-outline-secondary" href="<?= $root ?>/orders.php?f=<?= date('Y-m-d') ?>&t=<?= date('Y-m-d') ?>" title="<?= $l['Today\'s orders'] ?>">
			                        <span id="orders"><?= $orders ?></span> <i class="fas fa-shopping-bag"></i>
			                    </a>
			                    <a class="btn btn-sm btn-outline-secondary" href="#" title="<?= $l['After sales service'] ?>">
			                        <span id="messages"><?= $messages ?></span> <i class="fas fa-comment-alt"></i>
			                    </a>
							</div>
							<div class="btn-group mr-4">
								<a href="<?= $root ?>/plus.php" title="<?= $l['Add keys'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-plus"></i></a>
								<a href="<?= $root ?>/create.php" title="<?= $l['Create boxes'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-box-open"></i></a>
								<a href="<?= $root ?>/history.php" title="<?= $l['History'] ?>" class="btn btn-sm btn-outline-secondary"><i class="far fa-clock"></i></a>
								<a href="<?= $root ?>/settings.php" title="<?= $l['Settings'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-cog"></i></a>
							</div>
						</div>
						<?php endif; ?>
					</div>
					<?php if (!empty($alertTitle) || !empty($alertContent)): ?>
		            <div class="alert alert-warning" role="alert" id="myAlert">
		                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
		                    <i class="fas fa-times"></i>
		                    <span class="sr-only"><?= $l['Close'] ?></span>
		                </button>
		                <strong><?= $alertTitle ?></strong> <?= $alertContent ?>
		            </div>
		            <?php endif; ?>
					<?= $pageContent ?>
				</main>
			</div>
		</div>

		<!-- Fenêtres popup -->
		<div class="modal fade" id="myHelp" tabindex="-1" role="dialog">
		    <div class="modal-dialog" role="document">
		        <div class="modal-content">
		            <div class="modal-header">
		                <h4 class="modal-title" id="myModalLabel"><i class="fas fa-question"></i> <?= $l['Help'] ?></h4>
		                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		                    <i class="fas fa-times"></i>
		                    <span class="sr-only"><?= $l['Close'] ?></span>
		                </button>
		            </div>
		            <div class="modal-body">
		                <p></p>
		            </div>
		            <div class="modal-footer">
		                <button type="button" class="btn btn-primary-outline"><?= $l['yes'] ?></button>
		                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= $l['no'] ?></button>
		            </div>
		        </div>
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

	    //t = current time
	    //b = start value
	    //c = change in value
	    //d = duration
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
