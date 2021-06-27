<?php
header("Content-type: application/json");
try {
    $start_time = microtime(true);
    include "core.php";
    init_class(false, false);
    $exec_time = microtime(true) - $start_time;
    $server_side = ["status" => "ok", "status_msg" => null, "exec_time" => $exec_time, "user_info" => $user->info()];
} catch (Exception $e) {
    $server_side = ["status" => "error", "status_msg" => $e];
}
try {
    $client_side = ["status" => "ok", "status_msg" => null, "ip" => $tools->get_ip()];
} catch (Exception $e) {
    $server_side = ["status" => "error", "status_msg" => $e];
}
echo(json_encode(["client" => $client_side, "server" => $server_side]));
