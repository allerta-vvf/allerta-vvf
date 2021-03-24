<?php
include_once '../../core.php';
init_class();
$user->requirelogin(false);

if(isset($_POST["type"])){
    $type = $_POST["type"];
    $database->exec("INSERT INTO `%PREFIX%_type` (`name`) VALUES (:name);", false, [":name" => $type]);
    $user->log("Added service type");
}