<?php
require_once 'ui.php';
if($user->authenticated()){
  $tools->redirect("list.php");
}
$error = false;
if(isset($_POST['name']) & isset($_POST['password'])){
  $login = $user->login($_POST['name'], $_POST['password']);
  if($login===true){
    $tools->redirect("list.php");
  } else {
    $error = $login;
    bdump($error);
  }
}
loadtemplate('index.html', ['error' => $error, 'title' => t('Login',false)], false);