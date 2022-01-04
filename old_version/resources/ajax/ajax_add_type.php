<?php
include_once '../../core.php';
init_class();
$user->requirelogin(false);

if(isset($_POST["type"])){
    $type = $_POST["type"];
    $db->insert(
        DB_PREFIX."_type",
        ["name" => $type]
    );
    $user->log("Added service type");
}