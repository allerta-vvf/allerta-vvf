<?php
require_once 'ui.php';
$row = $db->select('SELECT * FROM `".DB_PREFIX."_profiles` WHERE id = :id', [":id" => $_GET['user']]);
loadtemplate('user_details.html', ['title' => t("Personal data", false), 'user' => $row[0]]);
?>
