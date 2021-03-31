<?php
include_once '../../core.php';
init_class(false);
$user->requirelogin(false);

$user_id = $user->auth->getUserId();
$result = $database->exec("SELECT * FROM `%PREFIX%_schedules` WHERE `user`={$user_id};", true);
if(!empty($result)){
    $result[0]["schedules"] = json_decode($result[0]["schedules"]);
}

if(isset($_POST["hours"])){
    $hours = (string) json_encode($_POST["hours"]);
    echo($hours);
    if(!empty($result)){
        $database->exec("UPDATE `%PREFIX%_schedules` SET `schedules` = :schedules WHERE `id` = :id;", false, [":id" => $result[0]["id"], ":schedules" => $hours]);
    } else {
        $database->exec("INSERT INTO `%PREFIX%_schedules` (`user`, `schedules`) VALUES (:user, :schedules);", false, [":user" => $user_id, ":schedules" => $hours]);
    }
} else {
    echo(json_encode(empty($result) ? [] : $result[0]));
}