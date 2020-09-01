<?php
require 'core.php';

init_class();
$user->requirelogin();
$id = $user->auth->getUserId();
$time = time();

if(!is_null($id)){
  $sql = "UPDATE `%PREFIX%_profiles` SET online_time = '$time' WHERE id = '" . $id ."'";
  $risultato = $database->exec($sql, true);
  echo(json_encode(["id" => $id, "time" => $time, "sql" => $sql]));
}
?>
