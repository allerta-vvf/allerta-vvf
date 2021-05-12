<?php
require_once 'ui.php';
$_SESSION["token_list"] = bin2hex(random_bytes(64));
if($JSless){
    $query_results = $db->select("SELECT * FROM `".DB_PREFIX."_profiles` ORDER BY available DESC, chief DESC, services ASC, availability_minutes ASC, name ASC");
} else {
    $query_results = null;
}
loadtemplate('list.html', ['title' => t("Availability List", false), 'token_list' => $_SESSION['token_list'], 'query_results' => $query_results]);
bdump($_SESSION);
