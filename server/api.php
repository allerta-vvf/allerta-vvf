<?php
require 'core.php';
use Spatie\ArrayToXml\ArrayToXml;

init_class(false);

$user_info = [];

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/healthcheck', function($vars)
    {
        header("Access-Control-Allow-Origin: *");
        return ["state" => "SUCCESS", "description" => ""];
    });
    $r->addRoute('POST', '/login', function($vars)
    {
        global $tools, $database, $user;
        try {
            $user->auth->loginWithUsername($_POST['username'], $_POST['password']);
            $apiKey = $tools->createKey(true);
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
    });
    $r->addRoute('GET', '/users', function($vars)
    {
        requireToken();
        global $database;
        $users = $database->exec("SELECT * FROM `%PREFIX%_users`;", true);
        $users_profiles = $database->exec("SELECT * FROM `%PREFIX%_profiles`;", true);
        foreach ($users_profiles as $key=>$value){
            if(is_null($users_profiles[$key]["name"])){
                $users_profiles[$key]["name"] = $users[$key]["username"];
            }
            $users_profiles[$key]["email"] = $users[$key]["email"];
        }
        return $users_profiles;
    });
    $r->addRoute('GET', '/user', function($vars)
    {
        requireToken();
        global $database, $user_info;
        $users = $database->exec("SELECT * FROM `%PREFIX%_users` WHERE id = :id;", true, [":id" => $user_info["id"]])[0];
        $users_profiles = $database->exec("SELECT * FROM `%PREFIX%_profiles` WHERE id = :id;", true, [":id" => $user_info["id"]])[0];
        if(is_null($users_profiles["name"])){
            $users_profiles["name"] = $users["username"];
        }
        $users_profiles["email"] = $users["email"];
        return $users_profiles;
    });
    $r->addRoute('GET', '/user/{id:\d+}', function($vars)
    {
        requireToken();
        global $database;
        $users = $database->exec("SELECT * FROM `%PREFIX%_users` WHERE id = :id;", true, [":id" => $vars["id"]])[0];
        $users_profiles = $database->exec("SELECT * FROM `%PREFIX%_profiles` WHERE id = :id;", true, [":id" => $vars["id"]])[0];
        if(is_null($users_profiles["name"])){
            $users_profiles["name"] = $users["username"];
        }
        $users_profiles["email"] = $users["email"];
        return $users_profiles;
    });
    $r->addRoute('GET', '/availability', function($vars)
    {
        requireToken();
        global $database, $user_info;
        return $database->exec("SELECT * FROM `%PREFIX%_profiles` WHERE id = :id;", true, [":id" => $user_info["id"]])[0]["available"];
    });
    $r->addRoute('GET', '/availability/{id:\d+}', function($vars)
    {
        requireToken();
        global $database;
        return $database->exec("SELECT * FROM `%PREFIX%_profiles` WHERE id = :id;", true, [":id" => $vars["id"]])[0]["available"];
    });
    $r->addRoute('GET', '/changeAvailability/{available:\d+}', function($vars)
    {
        requireToken();
        global $user, $database, $user_info;
        $vars["available"] = (int) $vars["available"];
        if($vars["available"] !== 0 && $vars["available"] !== 1) {
            return ["status" => "error", "message" => "Availability code not allowed"];
        }
        $user->log("Cambiamento disponibilita' (API) a ".$vars["available"], $user_info["id"], $user_info["id"], date("d/m/Y"), date("H:i.s"));
        $database->exec("UPDATE `%PREFIX%_profiles` SET `available` = :available WHERE `id` = :id;", true, [":id" => $user_info["id"], ":available" => $vars["available"]]);
    });
    $r->addRoute('GET', '/changeAvailability/{id:\d+}/{available:\d+}', function($vars)
    {
        requireToken();
        global $user, $database, $user_info;
        $vars["available"] = (int) $vars["available"];
        if($vars["available"] !== 0 && $vars["available"] !== 1) {
            return ["status" => "error", "message" => "Availability code not allowed"];
        }
        $user->log("Cambiamento disponibilita' (API) a ".$vars["available"], $vars["id"], $user_info["id"], date("d/m/Y"), date("H:i.s"));
        $database->exec("UPDATE `%PREFIX%_profiles` SET `available` = :available WHERE `id` = :id;", true, [":id" => $vars["id"], ":available" => $vars["available"]]);
    });
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$uri = str_replace("/allerta", "", $uri);
$uri = str_replace("api.php", "", $uri);
$uri = str_replace("//", "/", $uri);

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Get response format
if (isset($_GET["xml"])) {
    $response = "xml";
    $responseType = "application/xml";
} else if (isset($_GET["json"])) {
    $response = "json";
    $responseType = "application/json";
} else if (false !== strpos($uri, 'xml')) {
    $response = "xml";
    $responseType = "application/xml";
    $uri = str_replace(".xml", "", $uri);
} else if (false !== strpos($uri, 'json')) {
    $response = "json";
    $responseType = "application/json";
    $uri = str_replace(".json", "", $uri);
} else {
    $response = "json";
    $responseType = "application/json";
}

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

bdump($httpMethod, $uri);
bdump($response);

function responseApi($content, $status_code=200){
    global $response, $responseType;
    if($status_code !== 200){
        http_response_code($status_code);
    }
    header("Content-type: ".$responseType);
    if($response == "json"){
        echo(json_encode($content));
    } else {
        echo(ArrayToXml::convert($content));
    }
}

function validToken(){
    global $database, $user_info;
    $token = isset($_SERVER['HTTP_TOKEN']) ? $_SERVER['HTTP_TOKEN'] : (isset($_SERVER['HTTP_token']) ? $_SERVER['HTTP_token'] : (isset($_SERVER['HTTP_Token']) ? $_SERVER['HTTP_Token'] : (isset($_POST['TOKEN']) ? $_POST['TOKEN'] : false)));
    if($token == false){
        return false;
    }
    if(!empty($api_key_row = $database->exec("SELECT * FROM `%PREFIX%_api_keys` WHERE apikey = :apikey;", true, [":apikey" => $token]))){
        $user_info["id"] = $database->exec("SELECT * FROM `%PREFIX%_profiles` WHERE id = :id;", true, [":id" => $api_key_row[0]["user"]])[0]["id"];
        return true;
    } else {
        return false;
    }
}

function requireToken(){
    if(!validToken()){
        responseApi(["status" => "error", "message" => "Access Denied"], 401);
        exit();
    }
}
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        responseApi(["status" => "error", "message" => "Route not found"]);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        http_response_code(405);
        responseApi(["status" => "error", "message" => "Method not allowed"]);
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        responseApi($handler($vars));
        bdump($vars);
        break;
}