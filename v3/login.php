<?php
require 'inc/inits.php';

if (isset($_COOKIE['bundlemanager_auth']))
{
	$id = explode('---', $_COOKIE['bundlemanager_auth'])[0];
	$auth = explode('---', $_COOKIE['bundlemanager_auth'])[1];

	$user = $db->fetch("SELECT * FROM ".PREFIX_."employee WHERE id_employee = $id AND active = 1");

	if (sha1($user['email'].$user['passwd']) == $auth)
	{
		$logs->new('session', 'logged in with a cookie');

		$_SESSION['id_employee'] = $user['id_employee'];
		$_SESSION['firstname'] = $user['firstname'];
		$_SESSION['lastname'] = $user['lastname'];
		$_SESSION['email'] = $user['email'];
		$_SESSION['logged_in'] = true;
		header('Location:./index.php');
	}
}

extract($_POST);
if (!empty($email) && !empty($passwd)) {
	
	$result = $db->fetch("SELECT * FROM ".PREFIX_."employee WHERE email = '$email' AND active = 1");

	if (!count($result) || !password_verify($passwd, $result['passwd']))
	{
		$alertTitle = $l['Oops'];
		$alertContent = $l['Incorrect IDs'];
	}
	else if ($result['id_profile'] != 1)
	{
		$alertTitle = $l['Sorry'];
		$alertContent = $result['firstname'].", ".$l['you\'re not \'Super Admin\''];
	}
	else
	{
		if (!isset($_COOKIE['bundlemanager_auth']) && isset($remember))
		{
			setcookie('bundlemanager_auth', $result['id_employee'].'---'.sha1($result['email'].$result['passwd']), time() + 7*24*3600, null, null, false, true); // Valable 7 jours
		}

		$logs->new('session', 'logged in');

		$_SESSION['id_employee'] = $result['id_employee'];
		$_SESSION['firstname'] = $result['firstname'];
		$_SESSION['lastname'] = $result['lastname'];
		$_SESSION['email'] = $result['email'];
		$_SESSION['logged_in'] = true;
		header('Location:./index.php');
	}
}
	
ob_start(); // Page content
?>

<div class="alert alert-success alert-dismissible fade show" role="alert">
	<i class="fas fa-info-circle"></i> <?= $l['Use your Prestashop IDs to login.'] ?>
	<button type="button" class="close" data-dismiss="alert" aria-label="<?= $l['Close'] ?>">
		<span aria-hidden="true"><i class="fas fa-times"></i></span>
	</button>
</div>
<form class="form-inline" method="POST" style="text-align:center;margin-top:30px">
	<div class="col-auto">
		<div class="input-group">
			<div class="input-group-prepend">
	          <div class="input-group-text"><i class="fas fa-at"></i></div>
	        </div>
			<input type="email" class="form-control" name="email" placeholder="<?= $l['E-mail address'] ?>" required>
		</div>
	</div>
	<div class="col-auto">
		<div class="input-group">
			<div class="input-group-prepend">
	          <div class="input-group-text"><i class="fas fa-lock"></i></div>
	        </div>
			<input type="password" class="form-control" name="passwd" placeholder="<?= $l['Password'] ?>" required>
		</div>
	</div>
	<div class="col-auto">
		<input type="checkbox" name="remember"> <?= $l['Stay logged in'] ?>
		<button type="submit" class="btn btn-primary"><?= $l['Login'] ?></button>
	</div>
</form>

<?php
$pageContent = ob_get_clean();
$pageTitle = $l['Connection'];
require 'inc/default.php';
