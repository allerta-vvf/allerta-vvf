<?php
include_once '../../core.php';
init_class();
$user->requirelogin();
if(isset($_POST["change_id"]) && $_POST["dispo"] == 1){
     $risultato = $database->exec("UPDATE `%PREFIX%_profiles` SET `avaible` = '1' WHERE `%PREFIX%_profiles`.`id` = :id;", false, [":id" => $_POST["change_id"]]);
     $user->log("Attivazione disponibilita'", $_POST["change_id"], $user->auth->getUserId(), date("d/m/Y"), date("H:i.s"));
} else if(isset($_POST["change_id"]) && $_POST["dispo"] == 0){
     $risultato = $database->exec("UPDATE `%PREFIX%_profiles` SET `avaible` = '0' WHERE `%PREFIX%_profiles`.`id` = :id;", false, [":id" => $_POST["change_id"]]);
     $user->log("Rimozione disponibilita'", $_POST["change_id"], $user->auth->getUserId(), date("d/m/Y"), date("H:i.s"));
}
?>
