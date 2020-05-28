<?php
require_once 'ui.php';
if($user->autenticato()){
  $tools->redirect("lista.php");
}
$errore = false;
if(isset($_POST['name']) & isset($_POST['password'])){
  $login = $user->login($_POST['name'], $_POST['password']);
  if($login===true){
    $tools->redirect("lista.php");
  } else {
    $errore = $login;
    bdump($errore);
  }
}
loadtemplate('index.html', ['errore' => $errore, 'titolo' => 'Login'], false);