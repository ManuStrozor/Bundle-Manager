<?php
use \DateTime;

require 'inc/inits.php';

$_langs = array('fr', 'en');
$_sounds = scandir('sound');
$_sounds = array_diff($_sounds, ['.', '..', 'index.html']);

$notifs_sound_autoplay = false;

if (isset($_POST) && !empty($_POST))
{
	$now = new DateTime("NOW");
    $datenow = $now->format('Y-m-d H:i:s');

    extract($_POST);
    $prompt = trim($prompt);
    if ($setting == "language" && in_array($prompt, $_langs))
    {
    	$db->exec("UPDATE ".PREFIX_."configuration SET value = '$prompt', date_upd = '$datenow' WHERE name = 'BUNDLEMANAGER_LANG'");
    	$logs->new('options', 'language changed to '.$prompt);

    	header('Location:./settings.php');
    }
    else if ($setting == "notifs" && in_array($prompt, $_sounds))
    {
    	if (!empty($_FILES['upload']['size']))
    	{
    		$alertTitle = '<i class="fas fa-upload"></i>';
    		$errors = '';
			$file_name = $_FILES['upload']['name'];
			$file_size = $_FILES['upload']['size'];
			$file_tmp = $_FILES['upload']['tmp_name'];
			$file_ext = strtolower(end(explode('.', $file_name)));

			$extensions = array('wav');

			if(!in_array($file_ext, $extensions)){
				$errors .= "_Please choose a WAV file._";
			}

			if($file_size >= 2097152){
				$errors .= "_Size must be less than 2 MB._";
			}

			if(empty($errors)){
				move_uploaded_file($file_tmp, "../sound/".$file_name);
				header('Location:./settings.php');
			}else{
				$alertContent = $errors;
			}
    	}

    	$notifs_sound = $db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_NOTIFICATIONS_SOUND'");

    	if (isset($prompt) && $prompt != $notifs_sound['value'])
    	{
    		$db->exec("UPDATE ".PREFIX_."configuration SET value = '$prompt', date_upd = '$datenow' WHERE name = 'BUNDLEMANAGER_NOTIFICATIONS_SOUND'");
    		$logs->new('options', 'notifications sound changed to '.$prompt);
    	}
    	if (isset($toggle))
    	{
    		$db->exec("UPDATE ".PREFIX_."configuration SET value = $toggle, date_upd = '$datenow' WHERE name = 'BUNDLEMANAGER_NOTIFICATIONS_ENABLED'");
    		$logs->new('options', 'notifications changed to '.$toggle);
    		$notifs_sound_autoplay = $toggle;
    	}
    }
    else if ($setting == "debug")
    {
    	$error_reporting = $db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_ERROR_REPORTING'");

    	if (isset($prompt) && $prompt != $error_reporting['value'])
    	{
    		$db->exec("UPDATE ".PREFIX_."configuration SET value = '$prompt', date_upd = '$datenow' WHERE name = 'BUNDLEMANAGER_ERROR_REPORTING'");
    		$logs->new('options', 'error reporting changed to '.$prompt);
    	}
    	if (isset($toggle))
    	{
    		$db->exec("UPDATE ".PREFIX_."configuration SET value = $toggle, date_upd = '$datenow' WHERE name = 'BUNDLEMANAGER_DISPLAY_ERRORS'");
    		$logs->new('options', 'display errors changed to '.$toggle);
    	}

    	header('Location:./settings.php');
    }
    else if ($setting == "crypto")
    {
    	$db->exec("UPDATE ".PREFIX_."configuration SET value = '$prompt', date_upd = '$datenow' WHERE name = 'BUNDLEMANAGER_KEYCRYPT_PATH'");
    }
    else
	{
		$alertTitle = '<i class="fas fa-exclamation-triangle"></i>';
		$alertContent = $alert;
	}
}

$keycrypt = $db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_KEYCRYPT_PATH'");

$notifs_enabled = $db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_NOTIFICATIONS_ENABLED'");
$notifs_sound = $db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_NOTIFICATIONS_SOUND'");

$errors_enabled = $db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_DISPLAY_ERRORS'");
$error_reporting = $db->fetch("SELECT value FROM ".PREFIX_."configuration WHERE name = 'BUNDLEMANAGER_ERROR_REPORTING'");

ob_start(); // Page content
?>

<div style="display:flex;flex-wrap:wrap;">
	<!-- Langues -->
	<div class="card" style="width:20rem;margin:10px">
		<div class="card-body">
			<h5 class="card-title">
				<i class="fas fa-language"></i> <?= $l['International'] ?>
				<small class="text-muted"><a href="lang/"><?= $l['Translations'] ?></a></small>
			</h5>
			<h6 class="card-subtitle mb-2 text-muted">Ex: <em>
				<?php foreach ($_langs as $_lang): ?>
					<?= $_lang ?>,
				<?php endforeach; ?>
			</em></h6>
			<br>
			<form method="POST">
				<div class="form-group">
					<input type="text" class="form-control" value="<?= $lang['value'] ?>" name="prompt" required>
					<small class="form-text text-muted"><?= $l['Type the language tag and apply.'] ?></small>
				</div>
				<input type="hidden" name="setting" value="language">
				<input type="hidden" name="alert" value="<?= $l['Please enter a correct language tag.'] ?>">
				<button type="submit" class="btn btn-primary"><?= $l['Apply'] ?></button>
			</form>
		</div>
	</div>
	<!-- Notifications -->
	<div class="card" style="width:20rem;margin:10px">
		<div class="card-body">
			<h5 class="card-title">
				<i class="fas fa-bell"></i> <?= $l['Notifications'] ?>
				<i class="fas fa-toggle-<?= (!$notifs_enabled['value']) ? 'off' : 'on' ?> cursor-pointer" style="color:#007bff" onclick="window.document.toggleNotifs.submit()"></i>
			</h5>
			<h6 class="card-subtitle mb-2 text-muted">Ex: <em>
				<?php foreach ($_sounds as $_sound): ?>
					<?= $_sound ?>,
				<?php endforeach; ?>
			</em></h6>
			<br>
			<form method="POST" enctype="multipart/form-data">
				<input type="file" name="upload">
				<br><br>
				<div class="form-group">
					<input type="text" class="form-control" value="<?= $notifs_sound['value'] ?>" name="prompt" required>
					<small class="form-text text-muted"><?= $l['Type the file name ".wav" and apply.'] ?></small>
				</div>
				<input type="hidden" name="setting" value="notifs">
				<input type="hidden" name="alert" value="<?= $l['Audio file not found.'] ?>">
				<button type="submit" class="btn btn-primary"><?= $l['Apply'] ?></button>
			</form>
			<form method="POST" name="toggleNotifs">
				<input type="hidden" value="<?= $notifs_sound['value'] ?>" name="prompt">
				<input type="hidden" name="toggle" value="<?= (!$notifs_enabled['value']) ? 1 : 0 ?>">
				<input type="hidden" name="setting" value="notifs">
				<input type="hidden" name="alert" value="<?= $l['Audio file not found.'] ?>">
			</form>
			<?php if ($notifs_sound_autoplay): ?>
			<script>
				var audio = document.getElementById("notifs_audio");
				audio.volume = 0.2;
				audio.play();
			</script>
			<?php endif; ?>
		</div>
	</div>
	<!-- Debug -->
	<div class="card" style="width:25rem;margin:10px">
		<div class="card-body">
			<h5 class="card-title">
				<i class="fas fa-bug"></i> _Debug_
				<i class="fas fa-toggle-<?= (!$errors_enabled['value']) ? 'off' : 'on' ?> cursor-pointer" style="color:#007bff" onclick="window.document.toggleErrors.submit()"></i>
			</h5>
			<h6 class="card-subtitle mb-2 text-muted">Ex: <em>E_ALL & ~E_NOTICE & ~E_WARNING</em></h6>
			<br>
			<form method="POST">
				<div class="form-group">
<?php
$help = "E_ALL
E_ERROR
E_WARNING
E_PARSE
E_NOTICE
E_CORE_ERROR
E_CORE_WARNING
E_COMPILE_ERROR
E_COMPILE_WARNING
E_USER_ERROR
E_USER_WARNING
E_USER_NOTICE
E_STRICT
E_RECOVERABLE_ERROR
E_DEPRECATED
E_USER_DEPRECATED";
?>
					<input type="text" title="<?= $help ?>" class="form-control" value="<?= $error_reporting['value'] ?>" name="prompt">
					<small class="form-text text-muted"><?= $l['Type the error types to display'] ?></small>
				</div>
				<input type="hidden" name="setting" value="debug">
				<button type="submit" class="btn btn-primary"><?= $l['Apply'] ?></button>
			</form>
			<form method="POST" name="toggleErrors">
				<input type="hidden" value="<?= $error_reporting['value'] ?>" name="prompt">
				<input type="hidden" name="toggle" value="<?= (!$errors_enabled['value']) ? 1 : 0 ?>">
				<input type="hidden" name="setting" value="debug">
			</form>
		</div>
	</div>
	<!-- Chiffrement -->
	<div class="card" style="width:25rem;margin:10px">
		<div class="card-body">
			<h5 class="card-title"><i class="fas fa-file-code"></i> <?= $l['Encryption'] ?></h5>
			<h6 class="card-subtitle mb-2 text-muted">Ex: <em>modules/keymanager/keycrypt.php</em></h6>
			<br>
			<form method="POST">
				<div class="form-group">
					<input type="text" class="form-control" value="<?= $keycrypt['value'] ?>" name="prompt" required>
					<small class="form-text text-muted"><?= $l['Type the path to the "keycrypt" file and apply.'] ?></small>
				</div>
				<input type="hidden" name="setting" value="crypto">
				<button type="submit" class="btn btn-primary"><?= $l['Apply'] ?></button>
			</form>
		</div>
	</div>
</div>

<?php
$pageContent = ob_get_clean();
$pageTitle = $l['Settings'];
require 'inc/default.php';