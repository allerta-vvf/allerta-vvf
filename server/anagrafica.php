<?php
require_once 'ui.php';
loadtemplate('anagrafica.html', ['title' => t("Personal data",false), 'dacontrollare' => ucwords(str_replace('_', ' ', urldecode($_GET['user'])))]);
?>
