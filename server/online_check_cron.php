<?php
error_reporting(1);
$start = true;
$minuti = date('i');
include_once 'core.php';

init_class();

$minuti = date('i');
$sql = "UPDATE `%PREFIX%_users` SET online='0', online_time='0' WHERE online_time < '$minuti';";
$risultato = $database->exec($sql);
echo $sql;
?>
