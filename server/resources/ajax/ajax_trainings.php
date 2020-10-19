<?php
include_once("../../core.php");
init_class();
$user->requirelogin(false);

$risultato = $database->exec("SELECT * FROM `%PREFIX%_trainings` ORDER BY data DESC, inizio desc", true);

$response = [];
foreach($risultato as $row){
  $chief = $user->nameById($row["capo"]);

  $others_people_array = explode(",", $row['personale']);
  foreach($others_people_array as $key=>$name){
    $others_people_array[$key] = $user->nameById($name);
  }
  $others_people = implode(", ", $others_people_array);
  $response[] = [
    $row['data'],
    $row['name'],
    $row['inizio'],
    $row['fine'],
    $chief,
    $others_people,
    s($row['luogo'],false,true),
    s($row['note'],false,true),
    $database->getOption("training_edit") ? "<a href='edit_training.php?edit&id={$row['id']}'><i style='font-size: 40px' class='fa fa-edit'></i></a>" : null,
    $database->getOption("training_remove") ? "<a href='edit_training.php?delete&id={$row['id']}&increment={$row['increment']}'><i style='font-size: 40px' class='fa fa-trash'></i></a>" : null
  ];
}
header("Content-type: application/json");
print(json_encode($response));
?>