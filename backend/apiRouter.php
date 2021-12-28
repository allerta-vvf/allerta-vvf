<?php
require_once 'utils.php';
require_once 'cronRouter.php';

function apiRouter (FastRoute\RouteCollector $r) {
    $r->addGroup('/cron', function (FastRoute\RouteCollector $r) {
        cronRouter($r);
    });

    $r->addRoute(
        'GET',
        '/healthcheck',
        function ($vars) {
            apiResponse(["state" => "SUCCESS", "description" => ""]);
        }
    );
    $r->addRoute(
        ['GET', 'POST'],
        '/debug/request',
        function ($vars) {
            apiResponse(["get" => $_GET, "post" => $_POST, "server" => $_SERVER]);
        }
    );
    $r->addRoute(
        ['GET', 'POST'],
        '/debug/token',
        function ($vars) {
            global $users;
            $token = isset($_GET['token']) ? $_GET['token'] : $_POST['token'];
            $token_parsed = $users->auth->parseToken($token);

            $claims = $token_parsed !== false ? $token_parsed->claims() : null;
            $jti = isset($claims) ? $claims->get('jti') : null;
            $exp = isset($claims) ? $claims->get('exp') : null;
            $iat = isset($claims) ? $claims->get('iat') : null;
            $nbf = isset($claims) ? $claims->get('nbf') : null;
            $user_info = isset($claims) ? $claims->get('user_info') : null;

            apiResponse([
                "user_info" => $user_info,
                "jti" => $jti,
                "exp" => $exp,
                "iat" => $iat,
                "nbf" => $nbf,
                "valid" => $users->auth->isTokenValid($token_parsed),
            ]);
        }
    );

    $r->addRoute(
        ['GET'],
        '/list',
        function ($vars) {
            global $db, $users;
            requireLogin() || accessDenied();
            $users->online_time_update();
            if($users->hasRole(Role::FULL_VIEWER)) {
                $response = $db->select("SELECT * FROM `".DB_PREFIX."_profiles` ORDER BY available DESC, chief DESC, services ASC, availability_minutes ASC, name ASC");
            } else {
                $response = $db->select("SELECT `id`, `chief`, `online_time`, `available`, `name` FROM `".DB_PREFIX."_profiles` ORDER BY available DESC, chief DESC, services ASC, availability_minutes ASC, name ASC");
            }
            apiResponse(
                !is_null($response) ? $response : []
            );
        }
    );

    $r->addRoute(
        ['GET'],
        '/logs',
        function ($vars) {
            global $db, $users;
            requireLogin() || accessDenied();
            $users->online_time_update();
            $response = $db->select("SELECT * FROM `".DB_PREFIX."_log` ORDER BY `timestamp` DESC");
            if(!is_null($response)) {
                foreach($response as &$row) {
                    $row['changed'] = $users->getName($row['changed']);
                    $row['editor'] = $users->getName($row['editor']);
                }
            } else {
                $response = [];
            }
            apiResponse($response);
        }
    );

    $r->addRoute(
        ['GET'],
        '/services',
        function ($vars) {
            global $services, $users;
            requireLogin() || accessDenied();
            $users->online_time_update();
            apiResponse($services->list());
        }
    );
    $r->addRoute(
        ['POST'],
        '/services',
        function ($vars) {
            global $services, $users;
            requireLogin() || accessDenied();
            $users->online_time_update();
            apiResponse([]);
        }
    );

    $r->addRoute(
        ['GET'],
        '/trainings',
        function ($vars) {
            global $db, $users;
            requireLogin() || accessDenied();
            $users->online_time_update();
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
            global $users, $users;
            requireLogin() || accessDenied();
            $users->online_time_update();
            apiResponse($users->get_users());
        }
    );
    $r->addRoute(
        ['POST'],
        '/users',
        function ($vars) {
            global $users;
            requireLogin() || accessDenied();
            if(!$users->hasRole(Role::FULL_VIEWER) && $_POST["id"] !== $users->auth->getUserId()){
                exit;
            }
            apiResponse(["userId" => $users->add_user($_POST["email"], $_POST["name"], $_POST["username"], $_POST["password"], $_POST["phone_number"], $_POST["birthday"], $_POST["chief"], $_POST["driver"], $_POST["hidden"], $_POST["disabled"], "unknown")]);
        }
    );
    $r->addRoute(
        ['GET'],
        '/users/{userId}',
        function ($vars) {
            global $users;
            requireLogin() || accessDenied();
            if(!$users->hasRole(Role::FULL_VIEWER) && $_POST["id"] !== $users->auth->getUserId()){
                exit;
            }
            apiResponse($users->get_user($vars["userId"]));
        }
    );
    $r->addRoute(
        ['DELETE'],
        '/users/{userId}',
        function ($vars) {
            global $users;
            requireLogin() || accessDenied();
            if(!$users->hasRole(Role::FULL_VIEWER) && $_POST["id"] !== $users->auth->getUserId()){
                exit;
            }
            $users->remove_user($vars["userId"], "unknown");
            apiResponse(["status" => "success"]);
        }
    );

    $r->addRoute(
        ['GET'],
        '/availability',
        function ($vars) {
            global $users, $db;
            requireLogin() || accessDenied();
            $users->online_time_update();
            apiResponse([
                "available" => $db->selectValue(
                    "SELECT `available` FROM `".DB_PREFIX."_profiles` WHERE `id` = ?",
                    [$users->auth->getUserId()]
                )
            ]);
        }
    );
    $r->addRoute(
        ['POST'],
        '/availability',
        function ($vars) {
            global $users, $db;
            requireLogin() || accessDenied();
            $users->online_time_update();
            if(!$users->hasRole(Role::FULL_VIEWER) && $_POST["id"] !== $users->auth->getUserId()){
                exit;
            }
            logger("Disponibilità cambiata in ".($_POST["available"] ? '"disponibile"' : '"non disponibile"'), is_numeric($_POST["id"]) ? $_POST["id"] : $users->auth->getUserId(), $users->auth->getUserId());
            apiResponse([
                "response" => $db->update(
                    DB_PREFIX.'_profiles',
                    [
                        'available' => $_POST['available'],
                    ],
                    [
                        'id' => is_numeric($_POST["id"]) ? $_POST["id"] : $users->auth->getUserId()
                    ]
                )
            ]);
        }
    );

    $r->addRoute(
        ['POST'],
        '/login',
        function ($vars) {
            global $users;
            try {
                $token = $users->loginAndReturnToken($_POST["username"], $_POST["password"]);
                logger("Login effettuato");
                apiResponse(["status" => "success", "access_token" => $token]);
            }
            catch (\Delight\Auth\InvalidEmailException $e) {
                statusCode(401);
                apiResponse(["status" => "error", "message" => "Wrong email address"]);
            }
            catch (\Delight\Auth\InvalidPasswordException $e) {
                statusCode(401);
                apiResponse(["status" => "error", "message" => "Wrong password"]);
            }
            catch (\Delight\Auth\EmailNotVerifiedException $e) {
                statusCode(401);
                apiResponse(["status" => "error", "message" => "Email not verified"]);
            }
            catch (\Delight\Auth\UnknownUsernameException $e) {
                statusCode(401);
                apiResponse(["status" => "error", "message" => "Wrong username"]);
            }
            catch (\Delight\Auth\TooManyRequestsException $e) {
                statusCode(401);
                apiResponse(["status" => "error", "message" => "Too many requests"]);
            }
            catch (Exception $e) {
                statusCode(401);
                apiResponse(["status" => "error", "message" => "Unknown error", "error" => $e]);
            }
        }
    );
    $r->addRoute(
        ['GET', 'POST'],
        '/validateToken',
        function ($vars) {
            global $users;
            $token = isset($_GET['token']) ? $_GET['token'] : $_POST['token'];
            $token_parsed = $users->auth->parseToken($token);

            apiResponse([
                "valid" => $users->auth->isTokenValid($token_parsed),
            ]);
        }
    );
}
