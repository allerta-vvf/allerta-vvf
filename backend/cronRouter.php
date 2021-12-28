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
    global $db, $executed_actions;
}

function cronRouter (FastRoute\RouteCollector $r) {
    $r->addRoute(
        'GET',
        '/execute',
        function ($vars) {
            global $db, $executed_actions;
            $cron_job_allowed = true;

            if(!$cron_job_allowed) {
                statusCode(403);
                exit();
            }

            job_reset_availability();
            job_increment_availability();
            job_schedule_availability();

            apiResponse(["excuted_actions" => $executed_actions]);
        }
    );
}