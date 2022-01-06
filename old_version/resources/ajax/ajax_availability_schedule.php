<?php
include_once '../../core.php';
init_class(false);
$user->requirelogin(false);

$user_id = $user->auth->getUserId();
$result = $db->select("SELECT * FROM `".DB_PREFIX."_schedules` WHERE `user` = :id", ["id" => $user_id]);
if(!empty($result)){
    $result[0]["schedules"] = json_decode($result[0]["schedules"]);
    $result[0]["holidays"] = json_decode($result[0]["holidays"]);
}

if(isset($_POST["hours"]) && isset($_POST["holidays"])){
    $hours = (string) json_encode($_POST["hours"]);
    $holidays = (string) json_encode($_POST["holidays"]);
    echo($hours."-".$holidays);
    if(!empty($result)){
        $db->update(
            DB_PREFIX."_schedules",
            ["schedules" => $hours, "holidays" => $holidays],
            ["id" => $result[0]["id"]]
        );
    } else {
        $db->insert(
            DB_PREFIX."_schedules",
            ["schedules" => $hours, "holidays" => $holidays, "user" => $user_id]
        );
    }
} else {
    echo(json_encode(empty($result)||is_null($result) ? [] : $result[0]));
}