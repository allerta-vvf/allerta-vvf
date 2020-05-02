<?php
error_reporting(1);
$start = true;
$minuti = date('i');
include_once 'core.php';

init_class();

$minuti = date('i');
$sql = "UPDATE vigili SET online='0', online_time='0' WHERE online_time < '$minuti';";
#$sql = "UPDATE vigili SET online='0', online_time='0';";
$risultato = $database->esegui($sql);
echo $sql;
?>
