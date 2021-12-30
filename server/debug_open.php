<?php
require("core.php");
init_class();
if($user->authenticated()){
    if($user->hasRole(Role::DEVELOPER)){
        if(!isset($_REQUEST["op"]) || !isset($_REQUEST["id"])) $tools->rickroll();
        $openHandler = new DebugBar\OpenHandler($debugbar);
        $response = $openHandler->handle();
    } else {
        $tools->rickroll();
    }
} else {
    $tools->rickroll();
}