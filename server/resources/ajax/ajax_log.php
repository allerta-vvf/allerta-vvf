<?php
include_once("../../core.php");
init_class();
$user->requirelogin(false);
$user->online_time_update();

$risultato = $database->exec("SELECT * FROM `%PREFIX%_log`  ORDER BY `date` DESC, `time` DESC", true);

$hidden = $user->hidden();

$response = [];
foreach($risultato as $row){
  if(!in_array($row['changed'], $hidden) OR in_array($user->name(), $hidden)){
    $date = DateTime::createFromFormat("j/m/Y G:i.s", $row['date']." ".$row['time']);
    $date = $date->format('Y-m-d H:i:s');
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