<?php
require_once 'ui.php';
loadtemplate('user_details.html', ['title' => t("Personal data", false), 'view_user' => ucwords(str_replace('_', ' ', urldecode($_GET['user'])))]);
?>
