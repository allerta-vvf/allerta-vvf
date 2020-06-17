<?php
require_once 'ui.php';
loadtemplate('user_management.html', ['titolo' => 'Gestione Utenti']);
bdump($_SESSION);
