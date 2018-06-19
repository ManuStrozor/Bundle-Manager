<?php

if (!isset($go_uninstall)) {
    exit;
}

/* Delete tab */
$tabs = Db::getInstance()->ExecuteS("
    SELECT class_name
    FROM "._DB_PREFIX_."tab
    WHERE module LIKE '%".pSQL($this->name)."%'
");

foreach ($tabs as $t) {
    $idTab = (int)Tab::getIdFromClassName($t['class_name']);
    if ($idTab != 0) {
        $tab = new Tab($idTab);
        $tab->delete();
    }
}

/* Verification file */
$file = fopen(_PS_MODULE_DIR_.$this->name."/v3/inc/inits.php", "w");
$content = <<<EOT
<?php

header('Location:./notinstalled.php');

EOT;
fwrite($file, $content);
fclose($file);
