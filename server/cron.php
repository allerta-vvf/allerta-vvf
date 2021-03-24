<?php
require_once 'core.php';

init_class(false);
header('Content-Type: application/json');

function customErrorHandler(int $errNo, string $errMsg, string $file, int $line)
{
    $output = [
        "errNo" => $errNo,
        "error" => $errMsg,
        "file" => $file,
        "line" => $line
    ];
    $output_status = "error";
}
error_reporting(-1);
set_error_handler('customErrorHandler');

list($cronJobDay, $cronJobTime) = explode(";", $database->get_option("cron_job_time"));

$execDateTime = new stdClass();
$execDateTime->day = date('d');
$execDateTime->month = date('m');
$execDateTime->year = date("Y");
$execDateTime->hour = date('H');
$execDateTime->minutes = date('i');

$cronJobDateTime = new stdClass();
$cronJobDateTime->day = $cronJobDay;
$cronJobDateTime->month = date('m');
$cronJobDateTime->year = date("Y");
$cronJobDateTime->hour = explode(":", $cronJobTime)[0];
$cronJobDateTime->minutes = explode(":", $cronJobTime)[1];

$start = $database->get_option("cron_job_enabled") && ((isset($_POST['cron']) && $_POST['cron'] == "cron_job-".$database->get_option("cron_job_code")) || (isset($_SERVER['HTTP_CRON']) && $_SERVER['HTTP_CRON'] == "cron_job-".$database->get_option("cron_job_code")));
$start_reset = $execDateTime == $cronJobDateTime;

$action = "Availability Minutes ";
if($start) {
    if($start_reset) {
        $action .= "reset and ";
        $sql = "SELECT * FROM `%PREFIX%_profiles` WHERE `available` = 1 ";
        $profiles = $database->exec($sql, true);
        if(count($profiles) > 0) {
            $list = [];
            foreach($profiles as $profile){
                $list[] = [$profile["id"] => $profile["availability_minutes"]];
            }
            $database->exec("INSERT INTO `%PREFIX%_minutes` (`id`, `month`, `year`, `list`) VALUES (NULL, :month, :year, :list)", false, [":month" => $execDateTime->month,":year" => $execDateTime->year,":list"=>json_encode($list)]);
            $database->exec("UPDATE %PREFIX%_profiles SET availability_minutes = 0");
        }
    }
    $action .= "update";

    $sql = "SELECT * FROM `%PREFIX%_profiles` WHERE `available` = 1 ";
    $profiles = $database->exec($sql, true);
    if(count($profiles) > 0) {
        $output = [];
        $output[] = $profiles;
        $output_status = "ok";
        $queries = [];
        foreach ($profiles as $row) {
            $value = (int)$row["availability_minutes"]+5;
            $id = $row["id"];
            $increment[$id] = $value;
            $database->exec("UPDATE %PREFIX%_profiles SET availability_minutes = :value WHERE id = :id", true, [":value" => $value, ":id" => $id]);
            $tmp = $id . " - " . $value . " ";
            $tmp .= count($database->stmt->rowCount()) == 1 ? "success" : "fail";
            $queries[] = $tmp;
        }
        $output[] = $queries;
    } else {
        $output = ["profiles array empty"];
        $output_status = "ok";
    }
}

echo(json_encode(
    [
    "start" => $start,
    "start_reset" => $start_reset,
    "execDateTime" => $execDateTime,
    "cronJobDateTime" => $cronJobDateTime,
    "action" => $action,
    "output" => [
        "status" => $output_status,
        "message" => $output
    ]
    ]
));
