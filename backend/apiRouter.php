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

    $r->addRoute(
        ['GET'],
        '/availability',
        function ($vars) {
            global $users, $db;

            requireLogin() || accessDenied();

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
