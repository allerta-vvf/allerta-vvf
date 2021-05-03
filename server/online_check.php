<?php
require 'core.php';

init_class();
$user->requirelogin(false);
$id = $user->auth->getUserId();
$time = time();

if(!is_null($id)) {
    $db->update(
        DB_PREFIX."_profiles",
        ["online_time" => $time],
        ["id" => $id]
    );
    echo(json_encode(["id" => $id, "time" => $time, "sql" => $sql]));
}
?>
