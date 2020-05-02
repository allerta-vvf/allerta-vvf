<?php
error_reporting(1);
$start = true;
$minuti = date('i');

include_once 'core.php';

init_class();

$sql = "SELECT nome, online, online_time FROM vigili";
$risultato = $database->esegui($sql, true);
var_dump($risultato);
foreach($risultato as $row){
print("<pre>" . print_r($row, true) . "</pre>");
}

if(isset($_GET) && !is_null($_GET['utente'])){
  $sql = "UPDATE vigili SET online = '1', online_time = '$minuti' WHERE nome = '" . urldecode($_GET['utente']) . "'";
  $risultato = $database->esegui($sql, true);
  var_dump($risultato);
}
?>
