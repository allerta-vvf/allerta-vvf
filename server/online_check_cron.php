<?php
error_reporting(1);
$start = true;
$minuti = date('i');
include_once 'core.php';

init_class();

$minuti = date('i');
$sql = "UPDATE `%PREFIX%_vigili` SET online='0', online_time='0' WHERE online_time < '$minuti';";
$risultato = $database->esegui($sql);
echo $sql;
?>
