<?php
include_once '../../core.php';
init_class();
$user->requirelogin(false);
$user->online_time_update();

function generate_message($change_user, $action){
    global $tools, $user;
    if($action == "activate"){
        $action_string = "Thanks, %s, you have given %s in case of alert.";
    } else {
        $action_string = "Thanks, %s, you have removed %s in case of alert.";
    }
    if($change_user == $user->auth->getUserId()){
        $user_string = t("your availability", false);
    } else {
        $user_string = sprintf(t("%s availability", false), $user->nameById($change_user));
    }
    return sprintf(t($action_string, false), $user->nameById($user->auth->getUserId()), $user_string);
}

if(isset($_POST["change_id"]) && $_POST["dispo"] == 1 /* && $_POST["token_list"] == $_SESSION['token_list'] */){
    $db->update(
        DB_PREFIX."_profiles",
        ["available" => 1, "availability_last_change" => "manual"],
        ["id" => $_POST["change_id"]]
    );
    $user->log("Status changed to 'available'", $_POST["change_id"], $user->auth->getUserId());
    $message = generate_message($_POST["change_id"], "activate");
} else if(isset($_POST["change_id"]) && $_POST["dispo"] == 0 /* && $_POST["token_list"] == $_SESSION['token_list'] */){
    $db->update(
        DB_PREFIX."_profiles",
        ["available" => 0, "availability_last_change" => "manual"],
        ["id" => $_POST["change_id"]]
    );
    $user->log("Status changed to 'not available'", $_POST["change_id"], $user->auth->getUserId());
    $message = generate_message($_POST["change_id"], "deactivate");
}
echo(json_encode(["message" => $message]));
?>
