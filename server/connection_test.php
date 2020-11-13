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
    $server_info = $_SERVER;
    unset($server_info["DOCUMENT_ROOT"], $server_info["REQUEST_URI"], $server_info["SCRIPT_NAME"], $server_info["PHP_SELF"], $server_info["REMOTE_ADDR"], $server_info["REMOTE_PORT"], $server_info["SERVER_SOFTWARE"], $server_info["SERVER_NAME"], $server_info["SERVER_PORT"], $server_info["SCRIPT_FILENAME"]);
    $client_side = ["status" => "ok", "status_msg" => null, "info" => $server_info, "ip" => $tools->get_ip()];
} catch (Exception $e) {
    $server_side = ["status" => "error", "status_msg" => $e];
}
echo(json_encode(["client" => $client_side, "server" => $server_side]));
