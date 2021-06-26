<?php
include_once '../../core.php';
init_class();
$user->requirelogin(false);
$user->online_time_update();

function generate_message($change_user, $action){
    global $user;
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

if(!isset($_POST["change_id"]) || !isset($_POST["change_id"]) || !is_numeric($_POST["change_id"])){
    http_response_code(400);
    echo(json_encode(["message" => t("Bad request.",false)]));
    exit();
} else {
    $rows = $db->select(
        "SELECT available FROM ".DB_PREFIX."_profiles WHERE id = ?",
        [$_POST["change_id"]]
    );
    if(is_null($rows) || count($rows) !== 1) {
        http_response_code(400);
        echo(json_encode(["message" => t("Bad request.",false)." ".t("User not exists.",false)]));
        exit();
    }
}

if(!$user->hasRole(Role::FULL_VIEWER) && $_POST["change_id"] !== $user->auth->getUserId()){
    http_response_code(401);
    echo(json_encode(["message" => t("You are not authorized to perform this action.",false)]));
    exit();
}

if($_POST["dispo"] == 1 /* && $_POST["token_list"] == $_SESSION['token_list'] */){
    $db->update(
        DB_PREFIX."_profiles",
        ["available" => 1, "availability_last_change" => "manual"],
        ["id" => $_POST["change_id"]]
    );
    $user->log("Status changed to 'available'", $_POST["change_id"], $user->auth->getUserId());
    $message = generate_message($_POST["change_id"], "activate");
} else if($_POST["dispo"] == 0 /* && $_POST["token_list"] == $_SESSION['token_list'] */){
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
