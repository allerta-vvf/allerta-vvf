<?php
include_once("../../secure.php");
init_class();
$utente->richiedilogin();

$vigili_sql = "SELECT `id`, `nome`, `disponibile`, `caposquadra`, `autista`, `telefono`, `interventi`, `esercitazioni`, `online`, `minuti_dispo`, `immagine` FROM `vigili` LIMIT 0 , 30";
$vigili = $database->esegui($vigili_sql, true);

$interventi_sql="SELECT * FROM `interventi` ORDER BY `interventi`.`id` DESC LIMIT 0 , 30";
$interventi = $database->esegui($interventi_sql, true);

$esercitazioni_sql="SELECT * FROM `esercitazioni` ORDER BY `esercitazioni`.`id` DESC LIMIT 0 , 30";
$esercitazioni = $database->esegui($esercitazioni_sql, true);

$elenco = ["vigili" => $vigili, "interventi" => $interventi, "esercitazioni" => $esercitazioni];

header("Content-Type: application/json; charset=UTF-8");
echo(json_encode($elenco));