<?php
include_once("../../core.php");
init_class();
$user->requirelogin(false);

$risultato = $database->exec("SELECT * FROM `%PREFIX%_profiles` ORDER BY available DESC, chief DESC, services ASC, availability_minutes ASC, name ASC;", true);

$hidden = $user->hidden();

$response = [];
foreach($risultato as $row){
  if(!in_array($row['name'], $hidden) && ($row['hidden'] == 0 && $row['disabled'] == 0)){
    if(isset($_GET["only_data"])){
      if($user->requireRole(Role::FULL_VIEWER)){
        $response[] = [
          "id" => $row["id"],
          "available" => $row["available"],
          "chief" => $row['chief'],
          "online" => (time()-$row["online_time"])<=30 ? 1 : 0,
          "driver" => $row['autista'],
          "phone" => $row['telefono'],
          "services" => $row['services'],
          "availability_minutes" => $row['availability_minutes']
        ];
      } else {
        $response[] = [
          "id" => $row["id"],
          "available" => $row["available"],
          "online" => (time()-$row["online_time"])<=30 ? 1 : 0
        ];
      }
    } else {
      if($user->requireRole(Role::FULL_VIEWER)){
        $name = $user->nameById($row["id"]);
        $name_encoded = urlencode($name);
        $functionName = $row["available"] ? "Deactivate" : "Activate";
        $firstCell = $row["chief"] ? "<a onclick='$functionName(".$row["id"].");'><img alt='chief' src='./resources/images/red_helmet.png' width='20px'>$name</a>" : "<a onclick='$functionName(".$row["id"].");'><img alt='normal user' src='./resources/images/black_helmet.png' width='20px'>$name</a>";
        $secondCell = $row["available"] ? "<a onclick='$functionName(".$row["id"].");'><i class='fa fa-check' style='color:green'></i></a>" : "<a onclick='$functionName(".$row["id"].");'><i class='fa fa-times'  style='color:red'></i></a>";
        $response[] = [
          (time()-$row["online_time"])<=30 ? "<u>".$firstCell."</u>" : $firstCell,
          $secondCell,
          $row['autista'] ? "<img alt='driver' src='./resources/images/wheel.png' width='20px'>" : "",
          $row['telefono'] ? "<a href='tel:+".$row['telefono']."'><i class='fa fa-phone'></i></a>" : "",
          $row['telefono'] ? "<a href='https://api.whatsapp.com/send?phone=+".$row['telefono']."text=ALLERTA IN CORSO.%20Mettiti%20in%20contatto%20con%20$name_encoded'><i class='fa fa-whatsapp' style='color:green'></i></a>" : "",
          $row['services'],
          $row['availability_minutes'],
          "<a href='user_details.php?user=".$row['id']."'><p>".t("Altri dettagli", false)."</p></a>"
        ];
      } else {
        $response[] = [
          "id" => $row["id"],
          "available" => $row["available"],
          "online" => (time()-$row["online_time"])<=30 ? 1 : 0
        ];
      }
    }
  }
}
header("Content-type: application/json");
print(json_encode($response));
?>
