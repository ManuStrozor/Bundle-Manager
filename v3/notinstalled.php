<?php
ob_start(); // Page content
?>

<p>
	You have been redirected to this page because Bundle Manager is not installed.<br>
	If this is no longer the case, try accessing the <a href="./" title="Home">home page</a>.
</p>

<?php
$pageContent = ob_get_clean();
$pageTitle = 'Not installed';
require 'inc/default.php';
