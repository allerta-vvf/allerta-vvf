<?php
include_once("../../core.php");
init_class();
$user->requirelogin(false);
$user->online_time_update();

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
          "driver" => $row['driver'],
          "phone" => $row['phone_number'],
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
        $functionName = $row["available"] ? "deactivate" : "activate";
        $helmet_colour = $row["chief"] ? "red" : "black";
        $firstCell = "<a id='username-{$row['id']}' style='text-align: left;' onclick='$functionName(".$row["id"].");'><img alt='{$helmet_colour} helmet' src='./resources/images/{$helmet_colour}_helmet.png' width='20px'>$name</a>";
        $secondCell = $row["available"] ? "<a onclick='$functionName(".$row["id"].");'><i class='fa fa-check' style='color:green'></i></a>" : "<a onclick='$functionName(".$row["id"].");'><i class='fa fa-times' style='color:red'></i></a>";
        $response[] = [
          (time()-$row["online_time"])<=30 ? "<u>".$firstCell."</u>" : $firstCell,
          $secondCell,
          $row['driver'] ? "<img alt='driver' src='./resources/images/wheel.png' width='20px'>" : "",
          !empty($row['phone_number']) ? "<a href='tel:".$row['phone_number']."'><i class='fa fa-phone'></i></a>" : "",
          !empty($row['phone_number']) ? "<a href='https://api.whatsapp.com/send?phone=".$row['phone_number']."&text=ALLERTA IN CORSO.%20Mettiti%20in%20contatto%20con%20$name_encoded'><i class='fa fa-whatsapp' style='color:green'></i></a>" : "",
          $row['services'],
          $row['availability_minutes'],
          "<a href='user_details.php?user=".$row['id']."'><p>".t("Altri dettagli", false)."</p></a>"
        ];
      } else {
        $name = $user->nameById($row["id"]);
        $helmet_colour = $row["chief"] ? "red" : "black";
        $firstCell = "<a id='username-{$row['id']}' style='text-align: left;'><img alt='{$helmet_colour} helmet' src='./resources/images/{$helmet_colour}_helmet.png' width='20px'>$name</a>";
        $secondCell = $row["available"] ? "<a><i class='fa fa-check' style='color:green'></i></a>" : "<a><i class='fa fa-times' style='color:red'></i></a>";
        $response[] = [
          (time()-$row["online_time"])<=30 ? "<u>".$firstCell."</u>" : $firstCell,
          $secondCell
        ];
      }
    }
  }
}
$json_response = json_encode($response);
$response_data = substr(base64_encode($json_response), 0, 5);
header("data: ".$response_data);
header("Content-type: application/json");
if(isset($_GET["old_data"]) && $_GET["old_data"] !== $response_data){
  print($json_response);
} else {
  print("{}");
}
?>
