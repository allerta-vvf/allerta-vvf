<?php
require 'utils.php';
require 'apiRouter.php';

use Spatie\ArrayToXml\ArrayToXml;
$MIMEdetector = new League\MimeTypeDetection\ExtensionMimeTypeDetector();

$dispatcher = FastRoute\simpleDispatcher(
    function (FastRoute\RouteCollector $r) {
        $r->addGroup('/api', function (FastRoute\RouteCollector $r) {
            apiRouter($r);
        });

        $r->addRoute(
            'GET',
            '/',
            function ($vars) {
                header("Content-type: text/html");
                plainResponse(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "dist-frontend" . DIRECTORY_SEPARATOR . "index.html"));
            }
        );
        $r->addRoute(
            'GET',
            '/{file:.+}',
            function ($vars) {
                global $MIMEdetector;
                $filePath = __DIR__ . DIRECTORY_SEPARATOR . "dist-frontend" . DIRECTORY_SEPARATOR . $vars['file'];

                if (!file_exists($filePath)) {
                    notFoundErrorHandler();
                } else {
                    header("Content-type: " . $MIMEdetector->detectMimeTypeFromFile($filePath));
                    plainResponse(file_get_contents($filePath));
                }
            }
        );
    }
);

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
if(defined('BASE_PATH')){
    $uri = str_replace(BASE_PATH, "", $uri);
}
$uri = str_replace("index.php", "", $uri);
$uri = str_replace("///", "/", $uri);
$uri = str_replace("//", "/", $uri);
$uri = "/" . trim($uri, "/");

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
$statusCode = 200;

function statusCode($code)
{
    global $statusCode;
    $statusCode = $code;
}

function apiResponse($content)
{
    global $uri, $responseFormat, $statusCode;

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

    http_response_code($statusCode);
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: *");
    header("Access-Control-Max-Age: *");
    header("Content-type: " . $responseFormatType);

    if ($responseFormat == "json") {
        echo (json_encode($content));
    } else {
        echo (ArrayToXml::convert($content));
    }
}

//https://gist.github.com/wildiney/b0be69ff9960642b4f7d3ec2ff3ffb0b
function getAuthorizationHeader(){
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}
function getBearerToken() {
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

function requireLogin()
{
    global $users;
    $token = getBearerToken();
    if($users->auth->isTokenValid($token)) {
        $users->auth->authenticateWithToken($token);
        if($users->auth->hasRole(\Delight\Auth\Role::CONSULTANT)) {
            //Migrate to new user roles
            $users->auth->admin()->removeRoleForUserById($users->auth->getUserId(), \Delight\Auth\Role::CONSULTANT);
            $users->auth->admin()->addRoleForUserById($users->auth->getUserId(), Role::SUPER_EDITOR);

            $users->auth->authenticateWithToken($token);
        }
        if(defined('SENTRY_LOADED')) {
            \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($users): void {
                $scope->setUser([
                    'id' => $users->auth->getUserId(),
                    'username' => $users->auth->getUserName(),
                    'name' => $users->getName(),
                    'email' => $users->auth->getEmail(),
                    'ip_address' => get_ip()
                ]);
            });
        }
        return true;
    }
    return false;
}

function accessDenied()
{
    statusCode(401);
    apiResponse(["error" => "Access denied"]);
    exit();
}

function plainResponse($content)
{
    global $statusCode;
    http_response_code($statusCode);
    echo ($content);
}

function notFoundErrorHandler()
{
    global $uri;
    if (false !== strpos($uri, 'api')) {
        statusCode(404);
        apiResponse(["status" => "error", "message" => "Resource not found"]);
    } else {
        statusCode(404);
        header("Content-type: text/html");
        plainResponse(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "dist-frontend" . DIRECTORY_SEPARATOR . "index.html"));
    }
}

if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
    exit();
}

try {
    if(defined('SENTRY_LOADED')) {
        \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($uri): void {
            $scope->setTag('page.route', $uri);
        });
    }
    switch ($routeInfo[0]) {
        case FastRoute\Dispatcher::NOT_FOUND:
            notFoundErrorHandler();
            break;
        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            $allowedMethods = $routeInfo[1];
            http_response_code(405);
            apiResponse(["status" => "error", "message" => "Method not allowed", "usedMethod" => $_SERVER['REQUEST_METHOD']]);
            break;
        case FastRoute\Dispatcher::FOUND:
            $handler = $routeInfo[1];
            $vars = $routeInfo[2];
            $handler($vars);
            break;
    }    
} catch (\Throwable $exception) {
    \Sentry\captureException($exception);
}