<?php
include_once("../../core.php");
init_class();
$user->requirelogin(false);
$user->online_time_update();

$risultato = $database->exec("SELECT * FROM `%PREFIX%_log` ORDER BY `timestamp` DESC", true);

$hidden = $user->hidden();

//https://stackoverflow.com/a/2524761
function isValidTimeStamp($timestamp)
{
    return ((string) (int) $timestamp === $timestamp)
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
}

$response = [];
foreach($risultato as $row){
  if(!in_array($row['changed'], $hidden) OR in_array($user->name(), $hidden)){
    if(isValidTimeStamp($row["timestamp"])){
      $date = new DateTime();
      $date->setTimestamp($row["timestamp"]);
      $date = $date->format('Y-m-d H:i:s');
    } else {
      $date = $row["timestamp"];
    }
    if(!is_null($row["changed"])){
      $changedName = $user->nameById($row["changed"]);
    } else {
      $changedName = "N/A";
    }
    if(!is_null($row["editor"])){
      $editorName = $user->nameById($row["editor"]);
    } else {
      $editorName = "N/A";
    }
    $response[] = [
      $row["action"],
      $changedName,
      $editorName,
      $date
    ];
  }
}
$tools->ajax_page_response($response);
?>