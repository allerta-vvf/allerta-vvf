<?php
require_once 'ui.php';
if($JSless){
    $user->online_time_update();
    if(isset($_POST["action"]) && isset($_POST["user_id"]) && isset($_POST["token_list"]) && $_POST["token_list"] == $_SESSION["token_list"]){
        if(!$user->hasRole(Role::FULL_VIEWER) && $_POST["user_id"] !== $user->auth->getUserId()){
            http_response_code(401);
            t("You are not authorized to perform this action.");
            exit();
        }
        if($_POST["action"] == "activate"){
            $db->update(
                DB_PREFIX."_profiles",
                ["available" => 1, "availability_last_change" => "manual"],
                ["id" => $_POST["user_id"]]
            );
            $user->log("Status changed to 'available'", $_POST["user_id"], $user->auth->getUserId());
        } else if($_POST["action"] == "deactivate"){
            $db->update(
                DB_PREFIX."_profiles",
                ["available" => 0, "availability_last_change" => "manual"],
                ["id" => $_POST["user_id"]]
            );
            $user->log("Status changed to 'not available'", $_POST["user_id"], $user->auth->getUserId());
        }
    }
    $query_results = $db->select("SELECT * FROM `".DB_PREFIX."_profiles` ORDER BY available DESC, chief DESC, services ASC, availability_minutes ASC, name ASC");
} else {
    $query_results = null;
}
$_SESSION["token_list"] = bin2hex(random_bytes(64));
loadtemplate('list.html', ['title' => t("Availability List", false), 'token_list' => $_SESSION['token_list'], 'query_results' => $query_results]);
bdump($_SESSION);
