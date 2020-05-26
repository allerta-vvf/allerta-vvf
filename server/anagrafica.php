<?php
require_once 'ui.php';
loadtemplate('anagrafica.html', ['titolo' => 'Anagrafica user', 'dacontrollare' => ucwords(str_replace('_', ' ', urldecode($_GET['user'])))]);
?>
