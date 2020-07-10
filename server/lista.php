<?php
require_once 'ui.php';
loadtemplate('lista.html', ['titolo' => t("Availability List",false)]);
bdump($_SESSION);
