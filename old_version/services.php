<?php
require_once 'ui.php';
if($JSless){
    $user->online_time_update();
    $query_results = $db->select("SELECT * FROM `".DB_PREFIX."_services` ORDER BY date DESC, beginning DESC");
} else {
    $query_results = null;
}
loadtemplate('services.html', ['title' => t('Services', false), 'query_results' => $query_results]);
?>
