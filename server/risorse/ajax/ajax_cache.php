<?php
include_once("../../core.php");
init_class();
$user->requirelogin();

$users_sql = "SELECT `id`, `name`, `avaible`, `caposquadra`, `autista`, `telefono`, `interventi`, `esercitazioni`, `online`, `minuti_dispo`, `immagine` FROM `%PREFIX%_profiles` LIMIT 0 , 30";
$users = $database->exec($users_sql, true);

$interventi_sql="SELECT * FROM `%PREFIX%_interventi` ORDER BY `interventi`.`id` DESC LIMIT 0 , 30";
$interventi = $database->exec($interventi_sql, true);

$esercitazioni_sql="SELECT * FROM `%PREFIX%_esercitazioni` ORDER BY `esercitazioni`.`id` DESC LIMIT 0 , 30";
$esercitazioni = $database->exec($esercitazioni_sql, true);

$elenco = ["users" => $users, "interventi" => $interventi, "esercitazioni" => $esercitazioni];

header("Content-Type: application/json; charset=UTF-8");
echo(json_encode($elenco));