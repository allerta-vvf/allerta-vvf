<?php
include_once '../../core.php';
init_class();
$user->requirelogin(false);

$tmp_token = $tools->generateNonce(32);
$db->insert(
    DB_PREFIX."_bot_telegram",
    ["tmp_login_token" => $tmp_token, "user" => $user->auth->getUserId()]
);

echo(json_encode(["token" => $tmp_token, "url" => "https://t.me/". BOT_TELEGRAM_USERNAME ."?start={$tmp_token}"]));