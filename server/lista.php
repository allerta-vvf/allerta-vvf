<?php
require_once 'ui.php';
loadtemplate('lista.html', ['titolo' => 'Disponibilità']);
bdump($_SESSION);
