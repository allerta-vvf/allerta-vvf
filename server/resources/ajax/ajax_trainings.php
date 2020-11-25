<?php
include_once("../../core.php");
init_class();
$user->requirelogin(false);
$user->online_time_update();

$risultato = $database->exec("SELECT * FROM `%PREFIX%_trainings` ORDER BY date DESC, beginning desc", true);

$response = [];
foreach($risultato as $row){
  $chief = $user->nameById($row["chief"]);

  $others_crew_array = explode(",", $row['crew']);
  foreach($others_crew_array as $key=>$name){
    $others_crew_array[$key] = $user->nameById($name);
  }
  $others_crew = implode(", ", $others_crew_array);
  $response[] = [
    $row['date'],
    $row['name'],
    $row['beginning'],
    $row['end'],
    $chief,
    $others_crew,
    s($row['place'],false,true),
    s($row['notes'],false,true),
    $database->getOption("training_edit") ? "<a class='pjax_disable' href='edit_training.php?edit&id={$row['id']}'><i style='font-size: 40px' class='fa fa-edit'></i></a>" : null,
    $database->getOption("training_remove") ? "<a class='pjax_disable' href='edit_training.php?delete&id={$row['id']}&increment={$row['increment']}'><i style='font-size: 40px' class='fa fa-trash'></i></a>" : null
  ];
}
header("Content-type: application/json");
print(json_encode($response));
?>