<?php
require_once 'core.php';
init_class();

p_start("Load Twig");
$webpack_manifest = json_decode(
    file_get_contents(realpath("resources/dist/manifest.json")),
    true
);
try {
    $loader = new \Twig\Loader\FilesystemLoader('templates');
} catch (Exception $e) {
    $loader = new \Twig\Loader\FilesystemLoader('../templates');
}
$filter = new \Twig\TwigFilter(
    't', function ($string) {
        global $translations;
        return $translations->translate($string);
    }
);
$twig = new \Twig\Environment(
    $loader, [
    //'cache' => 'compilation'
    ]
);
$twig->addFilter($filter);
$function_option = new \Twig\TwigFunction(
    'option', function ($option) {
        global $database;
        return $database->getOption($option);
    }
);
$twig->addFunction($function_option);
$function_username = new \Twig\TwigFunction(
    'username', function ($id) {
        global $user;
        return $user->nameById($id);
    }
);
$twig->addFunction($function_username);
$function_resource = new \Twig\TwigFunction(
    'resource', function ($file) {
        global $webpack_manifest;
        return $webpack_manifest[$file];
    }
);
$twig->addFunction($function_resource);
p_stop();

$template = null;
function loadtemplate($templatename, $data, $requirelogin=true)
{
    global $database, $user, $twig, $template;
    p_start("Render Twig template");
    if($requirelogin) {
        $user->requirelogin();
    }
    $data['delete_caches'] = isset($_GET["deleteCache"]) || isset($_GET["unregisterSW"]) || isset($_GET["unregisterSWandDisable"]);
    $data['delete_service_workers'] = isset($_GET["unregisterSW"]) || isset($_GET["unregisterSWandDisable"]);
    $data['delete_service_workers_and_disable'] = isset($_GET["unregisterSWandDisable"]);
    $data['owner'] = $database->getOption("owner");
    $data['urlsoftware'] = $database->getOption("web_url");
    $data['user'] = $user->info();
    $data['enable_technical_support'] = $database->getOption("enable_technical_support");
    $data['technical_support_key'] = $database->getOption("technical_support_key");
    $data['technical_support_open'] = isset($_COOKIE["chat"]);
    if($database->getOption("use_custom_error_sound")) {
        $data['error_sound'] = "custom-error.mp3";
    } else {
        $data['error_sound'] = "error.mp3";
    }
    if($database->getOption("use_custom_error_image")) {
        $data['error_image'] = "custom-error.gif";
    } else {
        $data['error_image'] = "error.gif";
    }
    $template = $twig->load($templatename);
    if(isset($_SERVER["HTTP_X_PJAX"]) || isset($_GET["X_PJAX"]) || isset($_GET["_PJAX"])) {
        $data["pjax_requested"] = true;
        echo $template->renderBlock("pjax_content", $data);
    } else {
        echo $template->render($data);
    }
    p_stop();
}
