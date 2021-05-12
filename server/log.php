<?php
require_once 'ui.php';
if($JSless){
    $query_results = $db->select("SELECT * FROM `".DB_PREFIX."_log` ORDER BY `timestamp` DESC");
} else {
    $query_results = null;
}
loadtemplate('log.html', ['title' => t('Logs', false), 'query_results' => $query_results]);
