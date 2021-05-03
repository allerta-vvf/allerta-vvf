<?php
include_once '../../core.php';
init_class();
$user->requirelogin(false);
$user->online_time_update();

if(isset($_POST["change_id"]) && $_POST["dispo"] == 1 /* && $_POST["token_list"] == $_SESSION['token_list'] */){
    $db->update(
        DB_PREFIX."_profiles",
        ["available" => 1, "availability_last_change" => "manual"],
        ["id" => $_POST["change_id"]]
    );
    $user->log("Status changed to 'available'", $_POST["change_id"], $user->auth->getUserId());
} else if(isset($_POST["change_id"]) && $_POST["dispo"] == 0 /* && $_POST["token_list"] == $_SESSION['token_list'] */){
    $db->update(
        DB_PREFIX."_profiles",
        ["available" => 0, "availability_last_change" => "manual"],
        ["id" => $_POST["change_id"]]
    );
    $user->log("Status changed to 'not available'", $_POST["change_id"], $user->auth->getUserId());
}
?>
