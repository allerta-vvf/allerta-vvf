<?php
require_once 'core.php';
init_class();

try {
    $loader = new \Twig\Loader\FilesystemLoader('templates');
} catch (Exception $e) {
    $loader = new \Twig\Loader\FilesystemLoader('../templates');
}
$twig = new \Twig\Environment($loader, [
    //'cache' => 'compilation'
]);
$template = NULL;
function loadtemplate($templatename, $data, $requirelogin=true){
  global $database, $user, $twig, $template;
  if($requirelogin){
    $user->requirelogin();
  }
  $data['owner'] = $database->getOption("owner");
  $data['urlsoftware'] = $database->getOption("web_url");
  $data['user'] = $user->info();
  $data['enable_technical_support'] = $database->getOption("enable_technical_support");
  $data['technical_support_key'] = $database->getOption("technical_support_key");
  $data['technical_support_open'] = isset($_COOKIE["chat"]);
  if($database->getOption("use_custom_error_sound")){
    $data['error_sound'] = "custom-error.mp3";
  } else {
    $data['error_sound'] = "error.mp3";
  }
  if($database->getOption("use_custom_error_sound")){
    $data['error_image'] = "custom-error.gif";
  } else {
    $data['error_image'] = "error.gif";
  }
  $template = $twig->load($templatename);
  echo $template->render($data);
}
