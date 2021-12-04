<?php
require 'vendor\autoload.php';

use Spatie\ArrayToXml\ArrayToXml;
$MIMEdetector = new League\MimeTypeDetection\ExtensionMimeTypeDetector();

$dispatcher = FastRoute\simpleDispatcher(
    function (FastRoute\RouteCollector $r) {
        $r->addGroup('/api', function (RouteCollector $r) {
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
    $statusCode = 200;
}

function apiResponse($content)
{
    global $uri, $responseFormat;

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
    header("Content-type: " . $responseFormatType);

    if ($responseFormat == "json") {
        echo (json_encode($content));
    } else {
        echo (ArrayToXml::convert($content));
    }
}

function plainResponse($content)
{
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
        global $statusCode;
        http_response_code($statusCode);

        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        $handler($vars);
        break;
}
