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

$url_software = get_option("web_url");

p_start("Load Twig");
$webpack_manifest = json_decode(
    file_get_contents(realpath("resources/dist/assets-manifest.json")),
    true
);
try {
    $loader = new \Twig\Loader\FilesystemLoader('templates');
} catch (Exception $e) {
    $loader = new \Twig\Loader\FilesystemLoader('../templates');
}
$twig = new \Twig\Environment(
    $loader, [
    //'cache' => 'compilation'
    ]
);

$filter_translate = new \Twig\TwigFilter(
    't', function ($string) {
        global $translations;
        return $translations->translate($string);
    }
);
$twig->addFilter($filter_translate);

$function_option = new \Twig\TwigFunction(
    'option', "get_option"
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
        return $webpack_manifest[$file]["src"];
    }
);
$twig->addFunction($function_resource);

$function_script = new \Twig\TwigFunction(
    'script', function ($file, $onLoad=false) {
        global $url_software, $webpack_manifest;
        $script_url = $url_software . "/resources/dist/" . $webpack_manifest[$file]["src"];
        $script_integrity = $webpack_manifest[$file]["integrity"];

        $script_tag = "<script src='{$script_url}' integrity='{$script_integrity}' crossorigin='anonymous'";
        if($onLoad){
            $script_tag .= " onload='{$onLoad}'";
        }
        $script_tag .= "></script>";
        return $script_tag;
    }, ['is_safe' => ['html']]
);
$twig->addFunction($function_script);

$filter_minimize = new \Twig\TwigFilter(
    'minimize', function ($content) {
        if(isset($_REQUEST["skip_minify"])){
            return $content;
        } else {
            return Minifier\TinyMinify::html($content, [
                'collapse_whitespace' => true,
                'disable_comments' => true,
            ]);
        }
    }, ['is_safe' => ['html']]
);
$twig->addFilter($filter_minimize);
p_stop();

$template = null;
function loadtemplate($templatename, $data, $requirelogin=true)
{
    global $url_software, $user, $twig, $template, $enable_debugbar, $debugbarRenderer;
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
    $data['owner'] = get_option("owner");
    $data['urlsoftware'] = $url_software;
    $data['user'] = $user->info();
    $data['show_menu'] = !isset($_REQUEST["hide_menu"]);
    $data['show_footer'] = !isset($_REQUEST["hide_footer"]);
    if(get_option("use_custom_error_sound")) {
        $data['error_sound'] = "custom-error.mp3";
    } else {
        $data['error_sound'] = "error.mp3";
    }
    if(get_option("use_custom_error_image")) {
        $data['error_image'] = "custom-error.gif";
    } else {
        $data['error_image'] = "error.gif";
    }
    //TODO: replace this
    if($messages = get_option("messages")){
        try {
            $messages = json_decode($messages, true);
            if(isset($messages[$templatename])){
                $data["message"] = $messages[$templatename];
            } else if(isset($messages["loggedIn"]) && $user->auth->isLoggedIn()) {
                $data["message"] = $messages["loggedIn"];
            } else if(isset($messages["global"])) {
                $data["message"] = $messages["global"];
            } else {
                $data["message"] = false;
            }
        } catch (\Throwable $th) {
            $data["message"] = false;
        }
    } else {
        $data["message"] = false;
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
