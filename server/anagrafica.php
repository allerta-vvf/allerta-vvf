<?php
require_once 'core.php';
loadtemplate('anagrafica.html', ['titolo' => 'Anagrafica utente', 'dacontrollare' => ucwords(str_replace('_', ' ', urldecode($_GET['utente'])))]);
?>
