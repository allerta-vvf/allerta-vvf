<?php
include_once("../../core.php");
init_class();
$user->requirelogin(false);

$risultato = $database->exec("SELECT * FROM `%PREFIX%_services` ORDER BY data DESC, uscita DESC", true);

$response = [];
foreach($risultato as $row){
  $chief = $user->nameById($row["capo"]);

  $drivers_array = explode(",", $row['autisti']);
  foreach($drivers_array as $key=>$name){
    $drivers_array[$key] = $user->nameById($name);
  }
  $drivers = implode(", ", $drivers_array);

  $others_people_array = explode(",", $row['personale']);
  foreach($others_people_array as $key=>$name){
    $others_people_array[$key] = $user->nameById($name);
  }
  $others_people = implode(", ", $others_people_array);
  $response[] = [
    $row['data'],
    $row['codice'],
    $row['uscita'],
    $row['rientro'],
    $chief,
    $drivers,
    $others_people,
    s($row['luogo'],false,true),
    s($row['note'],false,true),
    s($row['tipo'],false,true),
    $database->getOption("service_edit") ? "<a href='edit_service.php?edit&id={$row['id']}'><i style='font-size: 40px' class='fa fa-edit'></i></a>" : null,
    $database->getOption("service_remove") ? "<a href='edit_service.php?delete&id={$row['id']}&increment={$row['increment']}'><i style='font-size: 40px' class='fa fa-trash'></i></a>" : null
  ];
}
header("Content-type: application/json");
print(json_encode($response));
?>