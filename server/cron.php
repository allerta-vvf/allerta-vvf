<?php
require_once 'core.php';

init_class(false);
error_reporting(-1);

list($cronJobDay, $cronJobTime) = explode(";", get_option("cron_job_time"));

$execDateTime = [
    "day" => date("d"),
    "month" => date("m"),
    "year" => date("Y"),
    "hour" => date("H"),
    "minutes" => date("i")
];

$cronJobDateTime = [
    "day" => $cronJobDay,
    "month" => date("m"),
    "year" => date("Y"),
    "hour" => explode(":", $cronJobTime)[0],
    "minutes" => explode(":", $cronJobTime)[1]
];

$start = get_option("cron_job_enabled") && ((isset($_POST['cron']) && $_POST['cron'] == "cron_job-".get_option("cron_job_code")) || (isset($_SERVER['HTTP_CRON']) && $_SERVER['HTTP_CRON'] == "cron_job-".get_option("cron_job_code")));
$start_reset = ( $execDateTime["day"] == $cronJobDateTime["day"] &&
    $execDateTime["day"] == $cronJobDateTime["day"] &&
    $execDateTime["month"] == $cronJobDateTime["month"] &&
    $execDateTime["year"] == $cronJobDateTime["year"] &&
    $execDateTime["hour"] == $cronJobDateTime["hour"] &&
    $execDateTime["minutes"] - $cronJobDateTime["minutes"] < 5 );

$action = "Availability Minutes ";
if($start) {
    if($start_reset) {
        $action .= "reset and ";
        $profiles = $db->select("SELECT * FROM `".DB_PREFIX."_profiles` WHERE `available` = 1 ");
        if(count($profiles) > 0) {
            $list = [];
            foreach($profiles as $profile){
                $list[] = [$profile["id"] => $profile["availability_minutes"]];
            }
            $db->insert(
                DB_PREFIX."_minutes",
                ["month" => $execDateTime["month"], "year" => $execDateTime["year"], "list"=>json_encode($list)]
            );
            $db->exec("UPDATE `".DB_PREFIX."_profiles` SET `availability_minutes` = 0");
        }
    }
    $action .= "update";

    $profiles = $db->select("SELECT * FROM `".DB_PREFIX."_profiles` WHERE `available` = 1");
    if(count($profiles) > 0) {
        $output = [];
        $output[] = $profiles;
        $output_status = "ok";
        $queries = [];
        foreach ($profiles as $row) {
            $value = (int)$row["availability_minutes"]+5;
            $id = $row["id"];
            $increment[$id] = $value;
            $count = $db->update(
                DB_PREFIX."_profiles",
                ["availability_minutes" => $value],
                ["id" => $id]
            );
            $tmp = $id . " - " . $value . " ";
            $tmp .= $count == 1 ? "success" : "fail";
            $queries[] = $tmp;
        }
        $output[] = $queries;
    } else {
        $output = ["profiles array empty"];
        $output_status = "ok";
    }

    $result = $db->select("SELECT * FROM `".DB_PREFIX."_schedules`;");
    $schedules_check = [];
    $schedules_users = [];
    $schedules_check["schedules"] = [];
    $schedules_check["users"] = [];
    if(!empty($result)){
        foreach ($result as $key => $value) {
            $result[$key]["schedules"] = json_decode($result[$key]["schedules"]);
        }
        $schedules_check["table"] = $result;
        foreach ($result as $key => $row) {
            $last_exec = $row["last_exec"];
            $last_exec = [
                "day" => (int) explode(";",$row["last_exec"])[0],
                "hour" => (int) explode(":",explode(";",$row["last_exec"])[1])[0],
                "minutes"  => (int) explode(":",$row["last_exec"])[1]
            ];
            $id = $row["id"];
            $user_id = $row["user"];
            $selected_holidays = json_decode($row["holidays"]);
            $selected_holidays_dates = [];
            foreach ($selected_holidays as $holiday){
                $selected_holidays_dates[] = $user->holidays->getHoliday($holiday)->format('Y-m-d');
            }
            foreach ($row["schedules"] as $key => $value) {
                $schedule = [
                    "day" => (int) $value[0]+1,
                    "hour" => (int) explode(":",$value[1])[0],
                    "minutes"  => (int) explode(":",$value[1])[1]
                ];
                $now = [
                    "day" => (int) date("N"),
                    "hour" => (int) date("H"),
                    "minutes"  => (int) date("i")
                ];

                if(
                    $schedule["day"] == $now["day"] &&
                    $schedule["hour"] == $now["hour"] &&
                    $schedule["minutes"] <= $now["minutes"] &&
                    $now["minutes"] - $schedule["minutes"] <= 30
                ){
                    if(!in_array($user_id,$schedules_users)) $schedules_users[] = $user_id;
                    if($schedule["hour"] == $last_exec["hour"] ? $schedule["minutes"] !== $last_exec["minutes"] : true && !in_array(date('Y-m-d'), $selected_holidays_dates)){
                        $last_exec_new = $schedule["day"].";".sprintf("%02d", $schedule["hour"]).":".sprintf("%02d", $schedule["minutes"]);
                        $db->update(
                            DB_PREFIX."_schedules",
                            ["last_exec" => $last_exec_new],
                            ["id" => $id]
                        );
                        $db->update(
                            DB_PREFIX."_profiles",
                            ["available" => '1', "availability_last_change" => "cron"],
                            ["id" => $user_id]
                        );
                        $schedules_check["schedules"][] = [
                            "schedule" => $schedule,
                            "now" => $now,
                            "exec" => $last_exec,
                            "last_exec_new" => $last_exec_new,
                        ];
                    }
                }

            }
        }
        $schedules_check["users"] = $schedules_users;
        $profiles = $db->select("SELECT id FROM `".DB_PREFIX."_profiles`");
        foreach ($profiles as $profile) {
            if(!in_array($profile["id"],$schedules_users)){
                $db->update(
                    DB_PREFIX."_profiles",
                    ["available" => 0],
                    ["availability_last_change" => "cron", "id" => $profile["id"]]
                );
            }
        }
    }
} else {
    require("error_page.php");
    show_error_page(405, "Method not allowed", "Allowed methods: POST");
    exit();
}

header('Content-Type: application/json');
echo(json_encode(
    [
    "start" => $start,
    "start_reset" => $start_reset,
    "execDateTime" => $execDateTime,
    "cronJobDateTime" => $cronJobDateTime,
    "action" => $action,
    "schedules_check" => $schedules_check,
    "output" => [
        "status" => $output_status,
        "message" => $output
    ]
    ]
));
