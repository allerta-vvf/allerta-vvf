<?php
include_once("../../core.php");
init_class();
$user->requirelogin(false);

$risultato = $database->exec("SELECT * FROM `%PREFIX%_log`  ORDER BY `date` DESC, `time` DESC", true);

$hidden = $user->hidden();

$response = [];
foreach($risultato as $row){
  if(!in_array($row['changed'], $hidden) OR in_array($user->name(), $hidden)){
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
      $row['date']." ".$row['time']
    ];
  }
}
header("Content-type: application/json");
print(json_encode($response));
?>