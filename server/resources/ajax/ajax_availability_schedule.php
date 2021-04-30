<?php
include_once '../../core.php';
init_class(false);
$user->requirelogin(false);

$user_id = $user->auth->getUserId();
$result = $database->exec("SELECT * FROM `%PREFIX%_schedules` WHERE `user`={$user_id};", true);
if(!empty($result)){
    $result[0]["schedules"] = json_decode($result[0]["schedules"]);
    $result[0]["holidays"] = json_decode($result[0]["holidays"]);
}

if(isset($_POST["hours"])){
    $hours = (string) json_encode($_POST["hours"]);
    $holidays = (string) json_encode($_POST["holidays"]);
    echo($hours."-".$holidays);
    if(!empty($result)){
        $database->exec("UPDATE `%PREFIX%_schedules` SET schedules = :schedules, holidays = :holidays WHERE `id` = :id;", false, [":id" => $result[0]["id"], ":schedules" => $hours, ":holidays" => $holidays]);
    } else {
        $database->exec("INSERT INTO `%PREFIX%_schedules` (`user`, `schedules`, `holidays`) VALUES (:user, :schedules, :holidays);", false, [":user" => $user_id, ":schedules" => $hours, ":holidays" => $holidays]);
    }
} else {
    echo(json_encode(empty($result) ? [] : $result[0]));
}