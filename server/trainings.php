<?php
require_once 'ui.php';
if($JSless){
    $query_results = $db->select("SELECT * FROM `".DB_PREFIX."_trainings` ORDER BY date DESC, beginning desc");
} else {
    $query_results = null;
}
loadtemplate('trainings.html', ['title' => t('Trainings', false), 'query_results' => $query_results]);
?>
