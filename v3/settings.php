<?php
use \DateTime;

require 'inc/inits.php';

$_langs = ['fr', 'en'];
$_sounds = array_diff(
	scandir('sound'),
	['.', '..', 'index.html']
);

$notifs_sound_autoplay = false;

if (isset($_POST) && !empty($_POST))
{
    $datenow = date("Y-m-d H:i:s");

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
    	$upload = $_FILES['upload'];
    	if (!empty($upload['size']))
    	{
			$ext = strtolower(end(explode('.', $upload['name'])));
			$errors = '';

			if (!in_array($ext, array('wav'))) $errors .= $l['Please choose a WAV file.'];

			if ($upload['size'] >= 2097152) $errors .= $l['Size must be less than 2 MB.'];

			if (empty($errors))
			{
				move_uploaded_file($upload['tmp_name'], "../sound/".$upload['name']);
				header('Location:./settings.php');
			}
			else
			{
				$alertType = 'warning';
				$alertTitle = '<i class="fas fa-upload"></i>';
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
		$alertType = 'warning';
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
	<div class="card m-1">
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
	<!-- /Langues -->

	<!-- Notifications -->
	<div class="card m-1">
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
	<!-- /Notifications -->

	<!-- Debug -->
	<div class="card m-1">
		<div class="card-body">
			<h5 class="card-title">
				<i class="fas fa-bug"></i> <?= $l['Debug'] ?>
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
	<!-- /Debug -->

	<!-- Chiffrement -->
	<div class="card m-1">
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
	<!-- /Chiffrement -->

	<!-- Storage -->
	<div class="card m-1">
		<div class="card-body">
			<h5 class="card-title"><i class="fas fa-hdd"></i> <?= $l['Backup Storage'] ?></h5>
			<h6 class="card-subtitle mb-2 text-muted"></h6>
			<br>
			<p>
				<?php
				function dirSize($path) {
				    $bytestotal = 0;
				    $path = realpath($path);
				    if($path!==false && $path!='' && file_exists($path)){
				        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
				            $bytestotal += $object->getSize();
				        }
				    }
				    return $bytestotal;
				}
				function printSize($title, $b, $round = 2) {
					$type = array('', 'K', 'M', 'G', 'T');
					$c = 0;
					while($b >= 1024) {
						$b /= 1024;
						$c++;
					}
					return $title.' : '.round($b, $round).' '.$type[$c].'o';
				}
				?>
				<?= printSize($l['Total used space'], dirSize('./backup/')) ?>
			</p>
		</div>
	</div>
	<!-- /Storage -->
</div>

<?php
$pageContent = ob_get_clean();
$pageTitle = $l['Settings'];
$arianList = array(
	$l['Dashboard'] => array('href' => './', 'icon' => '<i class="fas fa-tachometer-alt"></i>')
);
require 'inc/default.php';
