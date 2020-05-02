<?php
include_once '../../core.php';
init_class();
$utente->richiedilogin();
if(isset($_POST["nomeutenteattivato"]) && isset($_POST["nomeutenteattivatore"]) && $_POST["dispo"] == 1)	{
     $risultato = $database->esegui("UPDATE vigili SET disponibile = 1 WHERE nome = :nome", false, [":nome" => $_POST["nomeutenteattivato"]]);
     $utente->log("Attivazione disponibilita'", $_POST["nomeutenteattivato"], $_POST["nomeutenteattivatore"], date("d/m/Y"), date("H:i.s"));
} else if(isset($_POST["nomeutenteattivato"]) && isset($_POST["nomeutenteattivatore"]) && $_POST["dispo"] == 0){
     $risultato = $database->esegui("UPDATE vigili SET disponibile = 0 WHERE nome = :nome", false, [":nome" => $_POST["nomeutenteattivato"]]);
     $utente->log("Rimozione disponibilita'", $_POST["nomeutenteattivato"], $_POST["nomeutenteattivatore"], date("d/m/Y"), date("H:i.s"));
}
?>
