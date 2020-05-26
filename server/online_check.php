<?php
error_reporting(1);
$start = true;
$minuti = date('i');

include_once 'core.php';

init_class();

$sql = "SELECT name, online, online_time FROM `%PREFIX%_profiles`";
$risultato = $database->esegui($sql, true);
var_dump($risultato);
foreach($risultato as $row){
print("<pre>" . print_r($row, true) . "</pre>");
}

if(isset($_GET) && !is_null($_GET['user'])){
  $sql = "UPDATE `%PREFIX%_profiles` SET online = '1', online_time = '$minuti' WHERE name = '" . urldecode($_GET['user']) . "'";
  $risultato = $database->esegui($sql, true);
  var_dump($risultato);
}
?>
