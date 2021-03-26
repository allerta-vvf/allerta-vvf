<?php
require_once 'ui.php';
$row = $database->exec('SELECT * FROM `%PREFIX%_profiles` WHERE id = :id', true, array(":id" => $_GET['user']));
loadtemplate('user_details.html', ['title' => t("Personal data", false), 'user' => $row[0]]);
?>
