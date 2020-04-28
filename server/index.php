<?php
require_once 'core.php';
if($utente->autenticato()){
  $tools->redirect("lista.php");
}
$errore = false;
if(isset($_POST['nome']) & isset($_POST['password'])){
  $login = $utente->login($_POST['nome'], md5($_POST['password']));
  //var_dump($login); exit;
  if($login===true){
    $tools->redirect("lista.php");
  } else {
    $errore = $login;
  }
}
loadtemplate('index.html', ['errore' => $errore, 'titolo' => 'Login'], false);