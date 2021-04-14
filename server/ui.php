<?php
require_once 'core.php';
init_class();

if(!is_null($debugbar)){
    $enable_debugbar = true;
    $debugbarRenderer = $debugbar->getJavascriptRenderer("./vendor/maximebf/debugbar/src/DebugBar/Resources");
    $debugbarRenderer->disableVendor("jquery");
    $debugbarRenderer->setEnableJqueryNoConflict(false);
    $debugbarRenderer->setOpenHandlerUrl('debug_open.php');
} else {
    $enable_debugbar = false;
}

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
        return $database->get_option($option);
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
    global $database, $user, $twig, $template, $enable_debugbar, $debugbarRenderer;
    p_start("Render Twig template");
    if($requirelogin) {
        $user->requirelogin();
    }
    $data['delete_caches'] = isset($_GET["deleteCache"]) || isset($_GET["unregisterSW"]) || isset($_GET["unregisterSWandDisable"]);
    $data['delete_service_workers'] = isset($_GET["unregisterSW"]) || isset($_GET["unregisterSWandDisable"]);
    $data['delete_service_workers_and_disable'] = isset($_GET["unregisterSWandDisable"]);
    $data['enable_debug_bar'] = $enable_debugbar;
    $data['debug_bar_head'] = $enable_debugbar ? $debugbarRenderer->renderHead() : "";
    $data['debug_bar'] = $enable_debugbar ? $debugbarRenderer->render() : "";
    $data['owner'] = $database->get_option("owner");
    $data['urlsoftware'] = $database->get_option("web_url");
    $data['user'] = $user->info();
    $data['show_menu'] = !isset($_REQUEST["hide_menu"]);
    $data['show_footer'] = !isset($_REQUEST["hide_footer"]);
    if($database->get_option("use_custom_error_sound")) {
        $data['error_sound'] = "custom-error.mp3";
    } else {
        $data['error_sound'] = "error.mp3";
    }
    if($database->get_option("use_custom_error_image")) {
        $data['error_image'] = "custom-error.gif";
    } else {
        $data['error_image'] = "error.gif";
    }
    \header_remove('X-Frame-Options');
    $template = $twig->load($templatename);
    if(isset($_SERVER["HTTP_X_PJAX"]) || isset($_GET["X_PJAX"]) || isset($_GET["_PJAX"])) {
        $data["pjax_requested"] = true;
        echo $template->renderBlock("pjax_content", $data);
    } else {
        echo $template->render($data);
    }
    p_stop();
}
