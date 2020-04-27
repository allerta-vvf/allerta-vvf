<?php
require_once 'secure.php';
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
function loadtemplate($templatename, $data, $richiedilogin=true){
  global $utente, $twig, $template;
  if($richiedilogin){
    $utente->richiedilogin();
  }
  $data['distaccamento'] = DISTACCAMENTO;
  $data['urlsoftware'] = WEB_URL;
  $data['utente'] = $utente->info();
  $data['enable_technical_support'] = ENABLE_TECHNICAL_SUPPORT;
  $data['technical_support_key'] = TECHNICAL_SUPPORT_KEY;
  $data['technical_support_open'] = isset($_COOKIE["chat"]);
  $template = $twig->load($templatename);
  echo $template->render($data);
}
