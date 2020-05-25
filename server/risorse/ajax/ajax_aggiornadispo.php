<?php
include_once '../../core.php';
init_class();
$user->requirelogin();
if(isset($_POST["change_id"]) && $_POST["dispo"] == 1){
     $risultato = $database->esegui("UPDATE `%PREFIX%_profiles` SET `avaible` = '1' WHERE `allerta04_profiles`.`id` = :id;", false, [":id" => $_POST["change_id"]]);
     $user->log("Attivazione disponibilita'", $_POST["change_id"], $user->name(), date("d/m/Y"), date("H:i.s"));
} else if(isset($_POST["change_id"]) && $_POST["dispo"] == 0){
     $risultato = $database->esegui("UPDATE `%PREFIX%_profiles` SET `avaible` = '0' WHERE `allerta04_profiles`.`id` = :id;", false, [":id" => $_POST["change_id"]]);
     $user->log("Rimozione disponibilita'", $_POST["change_id"], $user->name(), date("d/m/Y"), date("H:i.s"));
}
?>
