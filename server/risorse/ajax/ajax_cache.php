<?php
include_once("../../core.php");
init_class();
$user->requirelogin();

$users_sql = "SELECT `id`, `name`, `available`, `caposquadra`, `autista`, `telefono`, `services`, `trainings`, `online`, `availability_minutes`, `immagine` FROM `%PREFIX%_profiles` LIMIT 0 , 30";
$users = $database->exec($users_sql, true);

$services_sql="SELECT * FROM `%PREFIX%_services` ORDER BY `services`.`id` DESC LIMIT 0 , 30";
$services = $database->exec($services_sql, true);

$trainings_sql="SELECT * FROM `%PREFIX%_trainings` ORDER BY `trainings`.`id` DESC LIMIT 0 , 30";
$trainings = $database->exec($trainings_sql, true);

$elenco = ["users" => $users, "services" => $services, "trainings" => $trainings];

header("Content-Type: application/json; charset=UTF-8");
echo(json_encode($elenco));