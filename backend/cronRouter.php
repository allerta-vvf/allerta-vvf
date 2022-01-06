<?php
require_once 'utils.php';

$executed_actions = [];

function job_reset_availability() {
    global $db, $executed_actions;
    $profiles = $db->select("SELECT * FROM `".DB_PREFIX."_profiles`");
    if(!is_null($profiles) && count($profiles) > 0) {
        $list = [];
        foreach($profiles as $profile){
            $list[] = [$profile["id"] => $profile["availability_minutes"]];
        }
        $db->insert(
            DB_PREFIX."_minutes",
            ["month" => date("m"), "year" => date("Y"), "list"=> json_encode($list)]
        );
        $db->exec("UPDATE `".DB_PREFIX."_profiles` SET `availability_minutes` = 0");
        $output = $list;
        $output_status = "ok";
    } else {
        $output = ["profiles array empty"];
        $output_status = "error";
    }
    $executed_actions[] = [
        "title" => "Reset availability minutes",
        "description" => "Reset availability minutes for all profiles",
        "output" => $output,
        "output_status" => $output_status
    ];
}

function job_increment_availability() {
    global $db, $executed_actions;
    $profiles = $db->select("SELECT * FROM `".DB_PREFIX."_profiles` WHERE `available` = 1");
    if(!is_null($profiles) && count($profiles) > 0) {
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
    $executed_actions[] = [
        "title" => "Increment availability minutes",
        "description" => "Increment availability minutes for all available profiles",
        "output" => $output,
        "output_status" => $output_status
    ];
}

function job_schedule_availability() {
    global $availability, $db, $executed_actions;
    $result = $db->select("SELECT * FROM `".DB_PREFIX."_schedules`;");
    $schedules_check = [];
    $schedules_users = [];
    $schedules_check["schedules"] = [];
    $schedules_check["users"] = [];
    if(!empty($result)){
        foreach ($result as $key => $value) {
            $result[$key]["schedules"] = json_decode($result[$key]["schedules"], true);
        }
        $schedules_check["table"] = $result;
        foreach ($result as $row) {
            if(!is_null($row["last_exec"])){
                $last_exec = [
                    "day" => (int) explode(";",$row["last_exec"])[0],
                    "hour" => (int) explode(":",explode(";",$row["last_exec"])[1])[0],
                    "minutes"  => (int) explode(":",$row["last_exec"])[1]
                ];
            } else {
                $last_exec = null;
            }
            
            $id = $row["id"];
            $user_id = $row["user"];
            /*
            $selected_holidays = json_decode($row["holidays"]);
            $selected_holidays_dates = [];
            foreach ($selected_holidays as $holiday){
                $selected_holidays_dates[] = $user->holidays->getHoliday($holiday)->format('Y-m-d');
            }
            */
            foreach ($row["schedules"] as $value) {
                $schedule = [
                    "day" => (int) $value["day"]+1,
                    "hour" => (int) explode(":",$value["hour"])[0],
                    "minutes"  => (int) explode(":",$value["hour"])[1]
                ];
                $now = [
                    "day" => (int) date("N"),
                    "hour" => (int) date("H"),
                    "minutes"  => (int) date("i")
                ];

                $manual_mode = $db->selectValue("SELECT `manual_mode` FROM `".DB_PREFIX."_profiles` WHERE `id` = ?", [$user_id]);
                if(
                    $manual_mode == 0 &&
                    $schedule["day"] == $now["day"] &&
                    $schedule["hour"] == $now["hour"] &&
                    $schedule["minutes"] <= $now["minutes"] &&
                    $now["minutes"] - $schedule["minutes"] <= 30
                ){
                    if(!in_array($user_id,$schedules_users)) $schedules_users[] = $user_id;
                    if(is_null($last_exec) || (is_array($last_exec) && $schedule["hour"] == $last_exec["hour"] ? $schedule["minutes"] !== $last_exec["minutes"] : true)/* && !in_array(date('Y-m-d'), $selected_holidays_dates)*/){
                        $last_exec_new = $schedule["day"].";".sprintf("%02d", $schedule["hour"]).":".sprintf("%02d", $schedule["minutes"]);
                        $db->update(
                            DB_PREFIX."_schedules",
                            ["last_exec" => $last_exec_new],
                            ["id" => $id]
                        );
                        $availability->change(1, $user_id, false);
                        $schedules_check["schedules"][] = [
                            "schedule" => $schedule,
                            "now" => $now,
                            "last_exec" => $last_exec,
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
                $availability->change(0, $profile["id"], false);
            }
        }
        $output = $schedules_check;
        $output_status = "ok";
    } else {
        $output = ["schedules array empty"];
        $output_status = "ok";
    }
    $executed_actions[] = [
        "title" => "Schedule availability",
        "description" => "Update availability for all users based on schedules",
        "output" => $output,
        "output_status" => $output_status
    ];
}

function cronRouter (FastRoute\RouteCollector $r) {
    $r->addRoute(
        'POST',
        '/execute',
        function ($vars) {
            global $db, $executed_actions;
            $cron_job_allowed = get_option("cron_job_enabled", false) && ((isset($_POST['cron']) && $_POST['cron'] == "cron_job-".get_option("cron_job_code")) || (isset($_SERVER['HTTP_CRON']) && $_SERVER['HTTP_CRON'] == "cron_job-".get_option("cron_job_code")));

            if(!$cron_job_allowed) {
                statusCode(403);
                exit();
            }

            job_schedule_availability();
            //job_reset_availability();
            job_increment_availability();

            apiResponse(["excuted_actions" => $executed_actions]);
        }
    );
}