<?php
require_once 'ui.php';
loadtemplate('list.html', ['title' => t("Availability List",false)]);
bdump($_SESSION);
