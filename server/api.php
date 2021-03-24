<?php
define('REQUEST_USING_API', true);
require 'core.php';
use Spatie\ArrayToXml\ArrayToXml;
use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberFormat;
use Brick\PhoneNumber\PhoneNumberParseException;

$user_info = [];

$dispatcher = FastRoute\simpleDispatcher(
    function (FastRoute\RouteCollector $r) {
        $r->addRoute(
            'GET', '/healthcheck', function ($vars) {
                return ["state" => "SUCCESS", "description" => ""];
            }
        );
        $r->addRoute(
            ['GET', 'POST'], '/requestDebug', function ($vars) {
                return ["get" => $_GET, "post" => $_POST, "server" => $_SERVER];
            }
        );
        $r->addRoute(
            'POST', '/login', function ($vars) {
                global $tools, $database, $user;
                try {
                    $user->auth->loginWithUsername($_POST['username'], $_POST['password']);
                    $apiKey = $tools->createKey();
                    $database->exec("INSERT INTO `%PREFIX%_api_keys` (`apikey`, `user`, `permissions`) VALUES (:apiKey, :userId, 'ALL');", true, [":apiKey" => $apiKey, ":userId" => $user->auth->getUserId()]);
                    return ["status" => "ok", "apiKey" => $apiKey];
                }
                catch (\Delight\Auth\UnknownUsernameException $e) {
                    http_response_code(401);
                    return ["status" => "error", "message" => "Username unknown"];
                }
                catch (\Delight\Auth\AmbiguousUsernameException $e) {
                    http_response_code(401);
                    return ["status" => "error", "message" => "Ambiguous Username"];
                }
                catch (\Delight\Auth\InvalidPasswordException $e) {
                    http_response_code(401);
                    return ["status" => "error", "message" => "Wrong password"];
                }
                catch (\Delight\Auth\EmailNotVerifiedException $e) {
                    http_response_code(401);
                    return ["status" => "error", "message" => "Email not verified"];
                }
                catch (\Delight\Auth\TooManyRequestsException $e) {
                    http_response_code(429);
                    return ["status" => "error", "message" => "Too many requests"];
                }
            }
        );
        $r->addRoute(
            'GET', '/users', function ($vars) {
                requireToken();
                global $database;
                $users = $database->exec("SELECT * FROM `%PREFIX%_users`;", true);
                $users_profiles = $database->exec("SELECT * FROM `%PREFIX%_profiles`;", true);
                foreach ($users_profiles as $key=>$value){
                    if(is_null($users_profiles[$key]["name"])) {
                        $users_profiles[$key]["name"] = $users[$key]["username"];
                    }
                    $users_profiles[$key]["email"] = $users[$key]["email"];
                }
                return $users_profiles;
            }
        );
        $r->addRoute(
            'GET', '/user', function ($vars) {
                requireToken();
                global $database, $user_info;
                $users = $database->exec("SELECT * FROM `%PREFIX%_users` WHERE id = :id;", true, [":id" => $user_info["id"]])[0];
                $users_profiles = $database->exec("SELECT * FROM `%PREFIX%_profiles` WHERE id = :id;", true, [":id" => $user_info["id"]])[0];
                if(is_null($users_profiles["name"])) {
                    $users_profiles["name"] = $users["username"];
                }
                $users_profiles["email"] = $users["email"];
                return $users_profiles;
            }
        );
        $r->addRoute(
            'GET', '/user/{id:\d+}', function ($vars) {
                requireToken();
                global $database;
                $users = $database->exec("SELECT * FROM `%PREFIX%_users` WHERE id = :id;", true, [":id" => $vars["id"]])[0];
                $users_profiles = $database->exec("SELECT * FROM `%PREFIX%_profiles` WHERE id = :id;", true, [":id" => $vars["id"]])[0];
                if(is_null($users_profiles["name"])) {
                    $users_profiles["name"] = $users["username"];
                }
                $users_profiles["email"] = $users["email"];
                return $users_profiles;
            }
        );
        $r->addRoute(
            'POST', '/user', function ($vars) {
                requireToken();
                global $user, $user_info;
                $chief = isset($_POST["chief"]) ? $_POST["chief"]==1 : false;
                $driver = isset($_POST["driver"]) ? $_POST["driver"]==1 : false;
                $hidden = isset($_POST["hidden"]) ? $_POST["hidden"]==1 : false;
                $disabled = isset($_POST["disabled"]) ? $_POST["disabled"]==1 : false;
                if(isset($_POST["mail"], $_POST["name"], $_POST["username"], $_POST["password"], $_POST["phone_number"], $_POST["birthday"])) {
                    try {
                        $phone_number = PhoneNumber::parse($_POST["phone_number"]);
                        if (!$phone_number->isValidNumber()) {
                            return ["status" => "error", "message" => "Bad phone number"];
                        } else {
                            $phone_number = $phone_number->format(PhoneNumberFormat::E164);
                        }
                    } catch (PhoneNumberParseException $e) {
                        return ["status" => "error", "message" => "Bad phone number"];
                    }
                    try{
                        $userId = $user->add_user($_POST["mail"], $_POST["name"], $_POST["username"], $_POST["password"], $phone_number, $_POST["birthday"], $chief, $driver, $hidden, $disabled, $user_info["id"]);
                    } catch (\Delight\Auth\InvalidEmailException $e) {
                        return ["status" => "error", "message" => "Invalid email address"];
                    } catch (\Delight\Auth\InvalidPasswordException $e) {
                        return ["status" => "error", "message" => "Invalid password"];
                    } catch (\Delight\Auth\UserAlreadyExistsException $e) {
                        return ["status" => "error", "message" => "User already exists"];
                    }
                    if($userId) {
                        return ["userId" => $userId];
                    } else {
                        return ["status" => "error", "message" => "Unknown error"];
                    }
                } else {
                    return ["status" => "error", "message" => "User info required"];
                }
            }
        );
        $r->addRoute(
            'GET', '/availability', function ($vars) {
                requireToken();
                global $database, $user_info;
                return $database->exec("SELECT * FROM `%PREFIX%_profiles` WHERE id = :id;", true, [":id" => $user_info["id"]])[0]["available"];
            }
        );
        $r->addRoute(
            'GET', '/availability/{id:\d+}', function ($vars) {
                requireToken();
                global $database;
                return $database->exec("SELECT * FROM `%PREFIX%_profiles` WHERE id = :id;", true, [":id" => $vars["id"]])[0]["available"];
            }
        );
        $r->addRoute(
            'GET', '/changeAvailability/{available:\d+}', function ($vars) {
                requireToken();
                global $user, $database, $user_info;
                $vars["available"] = (int) $vars["available"];
                if($vars["available"] !== 0 && $vars["available"] !== 1) {
                    return ["status" => "error", "message" => "Availability code not allowed"];
                }
                $log_message = $vars["available"] ? "Status changed to 'available'" : "Status changed to 'not available'";
                $database->exec("UPDATE `%PREFIX%_profiles` SET `available` = :available WHERE `id` = :id;", true, [":id" => $user_info["id"], ":available" => $vars["available"]]);
                $user->log($log_message);
            }
        );
        $r->addRoute(
            'GET', '/changeAvailability/{id:\d+}/{available:\d+}', function ($vars) {
                requireToken();
                global $user, $database, $user_info;
                $vars["available"] = (int) $vars["available"];
                if($vars["available"] !== 0 && $vars["available"] !== 1) {
                    return ["status" => "error", "message" => "Availability code not allowed"];
                }
                $log_message = $vars["available"] ? "Status changed to 'available'" : "Status changed to 'not available'";
                $database->exec("UPDATE `%PREFIX%_profiles` SET `available` = :available WHERE `id` = :id;", true, [":id" => $vars["id"], ":available" => $vars["available"]]);
                $user->log($log_message, $vars["id"], $user_info["id"]);
            }
        );
    }
);

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$uri = str_replace($_SERVER['SCRIPT_NAME'], "", $uri);
$uri = str_replace("///", "/", $uri);
$uri = str_replace("//", "/", $uri);
$uri = "/" . trim($uri, "/");

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Get response format
if (isset($_GET["xml"])) {
    $responseFormat = "xml";
    $responseFormatType = "application/xml";
} else if (isset($_GET["json"])) {
    $responseFormat = "json";
    $responseFormatType = "application/json";
} else if (false !== strpos($uri, 'xml')) {
    $responseFormat = "xml";
    $responseFormatType = "application/xml";
    $uri = str_replace(".xml", "", $uri);
} else if (false !== strpos($uri, 'json')) {
    $responseFormat = "json";
    $responseFormatType = "application/json";
    $uri = str_replace(".json", "", $uri);
} else {
    $responseFormat = "json";
    $responseFormatType = "application/json";
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Max-Age: *");
header("Content-type: ".$responseFormatType);
init_class(false, false); //initialize classes after Content-type header

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

function responseApi($content, $status_code=200)
{
    global $responseFormat, $responseFormatType;
    if($status_code !== 200) {
        http_response_code($status_code);
    }
    if($responseFormat == "json") {
        echo(json_encode($content));
    } else {
        echo(ArrayToXml::convert($content));
    }
}

function validToken()
{
    global $database, $user_info;
    $token = isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : (isset($_REQUEST['apikey']) ? $_REQUEST['apikey'] : (isset($_SERVER['HTTP_APIKEY']) ? $_SERVER['HTTP_APIKEY'] : false));
    if($token == false) {
        return false;
    }
    if(!empty($api_key_row = $database->exec("SELECT * FROM `%PREFIX%_api_keys` WHERE apikey = :apikey;", true, [":apikey" => $token]))) {
        $user_info["id"] = $database->exec("SELECT * FROM `%PREFIX%_profiles` WHERE id = :id;", true, [":id" => $api_key_row[0]["user"]])[0]["id"];
        return true;
    } else {
        return false;
    }
}

function requireToken()
{
    if(!validToken()) {
        responseApi(["status" => "error", "message" => "Access Denied"], 401);
        exit();
    }
}

if($_SERVER['REQUEST_METHOD'] == "OPTIONS"){
    exit();
}

switch ($routeInfo[0]) {
case FastRoute\Dispatcher::NOT_FOUND:
    http_response_code(404);
    responseApi(["status" => "error", "message" => "Route not found"]);
    break;
case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
    $allowedMethods = $routeInfo[1];
    http_response_code(405);
    responseApi(["status" => "error", "message" => "Method not allowed", "usedMethod" => $_SERVER['REQUEST_METHOD']]);
    break;
case FastRoute\Dispatcher::FOUND:
    $handler = $routeInfo[1];
    $vars = $routeInfo[2];
    responseApi($handler($vars));
    bdump($vars);
    break;
}
