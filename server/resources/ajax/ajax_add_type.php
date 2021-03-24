<?php
include_once '../../core.php';
init_class();
$user->requirelogin(false);

if(isset($_POST["type"])){
    $type = $_POST["type"];
    $risultato = $database->exec("INSERT INTO `%PREFIX%_type` (`name`) VALUES (:name);", false, [":name" => $type]);
    $user->log("Aggiunta tipologia intervento", $user->auth->getUserId(), $user->auth->getUserId());
}