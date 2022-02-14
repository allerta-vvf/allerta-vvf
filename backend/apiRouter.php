<?php
require_once 'utils.php';
require_once 'telegramBotRouter.php';
require_once 'cronRouter.php';

function apiRouter (FastRoute\RouteCollector $r) {
    $r->addGroup('/cron', function (FastRoute\RouteCollector $r) {
        cronRouter($r);
    });

    $r->addRoute(
        ['GET', 'POST'],
        '/bot/telegram',
        function ($vars) {
            telegramBotRouter();
        }
    );

    $r->addRoute(
        'GET',
        '/owner_image',
        function ($vars) {
            if(get_option("use_custom_owner_image", false)) {
                $owner_image = get_option("owner_image", false);
                if($owner_image) {
                    header('Cache-control: max-age='.(60*60*24*31));
                    header('Expires: '.gmdate(DATE_RFC1123,time()+60*60*24*31));
                    header('Content-Type: image/png');
                    readfile($owner_image);
                } else {
                    statusCode(404);
                }
            } else {
                header('Cache-control: max-age='.(60*60*24*31));
                header('Expires: '.gmdate(DATE_RFC1123,time()+60*60*24*31));
                header('Content-Type: image/png');
                readfile("dist-frontend/assets/img/owner.png");
            }
        }
    );
    $r->addRoute(
        'GET',
        '/place_image',
        function ($vars) {
            header('Cache-control: max-age='.(60*60*24*31));
            header('Expires: '.gmdate(DATE_RFC1123,time()+60*60*24*31));
            header('Content-Type: image/png');
            readfile("tmp/".md5($_GET["lat"].";".$_GET["lng"]).".jpg");
        }
    );

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
            requireLogin();
            $users->online_time_update();
            if($users->hasRole(Role::SUPER_EDITOR)) {
                $response = $db->select("SELECT * FROM `".DB_PREFIX."_profiles` WHERE `hidden` = 0 ORDER BY available DESC, chief DESC, services ASC, trainings DESC, availability_minutes ASC, name ASC");
            } else {
                $response = $db->select("SELECT `id`, `chief`, `online_time`, `available`, `availability_minutes`, `name`, `driver`, `services` FROM `".DB_PREFIX."_profiles` WHERE `hidden` = 0 ORDER BY available DESC, chief DESC, services ASC, trainings DESC, availability_minutes ASC, name ASC");
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
            requireLogin();
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
            requireLogin();
            $users->online_time_update();
            apiResponse($services->list());
        }
    );
    $r->addRoute(
        ['POST'],
        '/services',
        function ($vars) {
            global $services, $users;
            requireLogin();
            $users->online_time_update();
            apiResponse(["response" => $services->add($_POST["start"], $_POST["end"], $_POST["code"], $_POST["chief"], $_POST["drivers"], $_POST["crew"], $_POST["place"], $_POST["notes"], $_POST["type"], $users->auth->getUserId())]);
        }
    );

    $r->addRoute(
        ['GET'],
        '/services/{id}',
        function ($vars) {
            global $services, $users;
            requireLogin();
            $users->online_time_update();
            apiResponse($services->get($vars['id']));
        }
    );
    $r->addRoute(
        ['DELETE'],
        '/services/{id}',
        function ($vars) {
            global $services, $users;
            requireLogin();
            $users->online_time_update();
            apiResponse(["response" => $services->delete($vars["id"])]);
        }
    );

    $r->addRoute(
        ['GET'],
        '/place_details',
        function ($vars) {
            global $db, $users;
            requireLogin();
            $users->online_time_update();
            $response = $db->selectRow("SELECT * FROM `".DB_PREFIX."_places_info` WHERE `lat` = ? and `lng` = ? LIMIT 0,1;", [$_GET["lat"], $_GET["lng"]]);
            apiResponse(!is_null($response) ? $response : []);
        }
    );

    $r->addRoute(
        ['GET'],
        '/trainings',
        function ($vars) {
            global $db, $users;
            requireLogin();
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
            requireLogin();
            $users->online_time_update();
            apiResponse($users->get_users());
        }
    );
    $r->addRoute(
        ['POST'],
        '/users',
        function ($vars) {
            global $users;
            requireLogin();
            if(!$users->hasRole(Role::SUPER_EDITOR) && $_POST["id"] !== $users->auth->getUserId()){
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
            requireLogin();
            if(!$users->hasRole(Role::SUPER_EDITOR) && $_POST["id"] !== $users->auth->getUserId()){
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
            requireLogin();
            if(!$users->hasRole(Role::SUPER_EDITOR) && $_POST["id"] !== $users->auth->getUserId()){
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
            requireLogin();
            $users->online_time_update();
            $row = $db->selectRow(
                "SELECT `available`, `manual_mode` FROM `".DB_PREFIX."_profiles` WHERE `id` = ?",
                [$users->auth->getUserId()]
            );
            apiResponse([
                "available" => $row["available"],
                "manual_mode" => $row["manual_mode"]
            ]);
        }
    );
    $r->addRoute(
        ['POST'],
        '/availability',
        function ($vars) {
            global $users, $availability;
            requireLogin();
            $users->online_time_update();
            if(!$users->hasRole(Role::SUPER_EDITOR) && (int) $_POST["id"] !== $users->auth->getUserId()){
                statusCode(401);
                apiResponse(["status" => "error", "message" => "You don't have permission to change other users availability", "t" => $users->auth->getUserId()]);
                return;
            }
            $user_id = is_numeric($_POST["id"]) ? $_POST["id"] : $users->auth->getUserId();
            apiResponse([
                "response" => $availability->change($_POST["available"], $user_id, true),
                "updated_user" => $user_id,
                "updated_user_name" => $users->getName($user_id)
            ]);
        }
    );
    $r->addRoute(
        "POST",
        "/manual_mode",
        function ($vars) {
            global $users, $availability;
            requireLogin();
            $users->online_time_update();
            $availability->change_manual_mode($_POST["manual_mode"]);
            apiResponse(["status" => "success"]);
        }
    );

    $r->addRoute(
        ['GET'],
        '/schedules',
        function ($vars) {
            global $users, $schedules;
            requireLogin();
            $users->online_time_update();
            apiResponse($schedules->get());
        }
    );
    $r->addRoute(
        ['POST'],
        '/schedules',
        function ($vars) {
            global $users, $schedules;
            requireLogin();
            $users->online_time_update();
            $new_schedules = !is_string($_POST["schedules"]) ? json_encode($_POST["schedules"]) : $_POST["schedules"];
            apiResponse([
                "response" => $schedules->update($new_schedules)
            ]);
        }
    );

    $r->addRoute(
        ['GET'],
        '/service_types',
        function ($vars) {
            global $users, $db;
            requireLogin();
            $users->online_time_update();
            $response = $db->select("SELECT * FROM `".DB_PREFIX."_type`");
            apiResponse(is_null($response) ? [] : $response);
        }
    );
    $r->addRoute(
        ['POST'],
        '/service_types',
        function ($vars) {
            global $users, $db;
            requireLogin();
            $users->online_time_update();
            $response = $db->insert(DB_PREFIX."_type", ["name" => $_POST["name"]]);
            apiResponse($response);
        }
    );

    $r->addRoute(
        ['GET'],
        '/places/search',
        function ($vars) {
            global $places;
            requireLogin();
            apiResponse($places->search($_GET["q"]));
        }
    );

    $r->addRoute(
        ['POST'],
        '/telegram_login_token',
        function ($vars) {
            global $users, $db;
            requireLogin();
            $users->online_time_update();
            $token = bin2hex(random_bytes(16));
            apiResponse([
                "response" => $db->insert(
                    DB_PREFIX.'_bot_telegram',
                    [
                        'user' => $users->auth->getUserId(),
                        'tmp_login_token' => $token
                    ]
                ),
                "start_link" => "https://t.me/".BOT_TELEGRAM_USERNAME."?start=".$token,
                "token" => $token
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
        ['POST'],
        '/impersonate',
        function ($vars) {
            global $users;
            requireLogin();

            if(!$users->hasRole(Role::SUPER_ADMIN)) {
                statusCode(401);
                apiResponse(["status" => "error", "message" => "You don't have permission to impersonate"]);
                return;
            }

            try {
                $token = $users->loginAsUserIdAndReturnToken($_POST["user_id"]);
                apiResponse(["status" => "success", "access_token" => $token]);
            }
            catch (\Delight\Auth\UnknownIdException $e) {
                statusCode(400);
                apiResponse(["status" => "error", "message" => "Wrong user ID"]);
            }
            catch (\Delight\Auth\EmailNotVerifiedException $e) {
                statusCode(400);
                apiResponse(["status" => "error", "message" => "Email not verified"]);
            }
            catch (Exception $e) {
                statusCode(400);
                apiResponse(["status" => "error", "message" => "Unknown error", "error" => $e]);
            }
        }
    );
    $r->addRoute(
        ['GET', 'POST'],
        '/refreshToken',
        function ($vars) {
            global $users;
            requireLogin(false);

            apiResponse([
                "token" => $users->generateToken()
            ]);
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
