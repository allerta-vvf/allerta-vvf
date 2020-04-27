<?php
include("secure.php");
init_class();
$utente->logout();
$tools->redirect("index.php");
?>
