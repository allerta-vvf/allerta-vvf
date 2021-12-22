<?php
require_once 'utils.php';
function apiRouter (FastRoute\RouteCollector $r) {
    $r->addRoute(
        'GET',
        '/healthcheck',
        function ($vars) {
            apiResponse(["state" => "SUCCESS", "description" => ""]);
        }
    );
    $r->addRoute(
        ['GET', 'POST'],
        '/requestDebug',
        function ($vars) {
            apiResponse(["get" => $_GET, "post" => $_POST, "server" => $_SERVER]);
        }
    );

    $r->addRoute(
        ['GET'],
        '/list',
        function ($vars) {
            global $db;
            $response = $db->select("SELECT * FROM `".DB_PREFIX."_profiles` ORDER BY available DESC, chief DESC, services ASC, availability_minutes ASC, name ASC");
            apiResponse(
                !is_null($response) ? $response : []
            );
        }
    );

    $r->addRoute(
        ['GET'],
        '/logs',
        function ($vars) {
            global $db;
            $response = $db->select("SELECT * FROM `".DB_PREFIX."_log` ORDER BY `timestamp` DESC");
            apiResponse(
                !is_null($response) ? $response : []
            );
        }
    );

    $r->addRoute(
        ['GET'],
        '/services',
        function ($vars) {
            global $services;
            apiResponse($services->list());
        }
    );
    $r->addRoute(
        ['POST'],
        '/services',
        function ($vars) {
            global $services;
            apiResponse([]);
        }
    );

    $r->addRoute(
        ['GET'],
        '/trainings',
        function ($vars) {
            global $db;
            $response = $db->select("SELECT * FROM `".DB_PREFIX."_trainings` ORDER BY date DESC, beginning desc");
            apiResponse(
                !is_null($response) ? $response : []
            );
        }
    );

    $r->addRoute(
        ['GET'],
        '/users',
        function ($vars) {
            global $users;
            apiResponse($users->get_users());
        }
    );
    $r->addRoute(
        ['POST'],
        '/users',
        function ($vars) {
            global $users;
            apiResponse(["userId" => $users->add_user($_POST["email"], $_POST["name"], $_POST["username"], $_POST["password"], $_POST["phone_number"], $_POST["birthday"], $_POST["chief"], $_POST["driver"], $_POST["hidden"], $_POST["disabled"], "unknown")]);
        }
    );
    $r->addRoute(
        ['GET'],
        '/users/{userId}',
        function ($vars) {
            global $users;
            apiResponse($users->get_user($vars["userId"]));
        }
    );
    $r->addRoute(
        ['DELETE'],
        '/users/{userId}',
        function ($vars) {
            global $users;
            $users->remove_user($vars["userId"], "unknown");
            apiResponse(["status" => "success"]);
        }
    );
}
