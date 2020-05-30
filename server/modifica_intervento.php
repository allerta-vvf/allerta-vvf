<?php
require_once 'ui.php';
if($tools->validate_form_data('$post-mod', true, "add")) {
  bdump("per poco...");
  if($tools->validate_form_data(['$post-data', '$post-codice', '$post-uscita', '$post-rientro', '$post-capo', '$post-luogo', '$post-note', '$post-tipo', '$post-token'])) {
    if($_POST["token"] == $_SESSION['token']){
      bdump("aggiungo intervento");
      $database->add_intervento($_POST["data"], $_POST["codice"], $_POST["uscita"], $_POST["rientro"], $_POST["capo"], $tools->extract_unique($_POST["autisti"]), $tools->extract_unique($_POST["personale"]), $_POST["luogo"], $_POST["note"], $_POST["tipo"], $tools->extract_unique([$_POST["capo"],$_POST["autisti"],$_POST["personale"]]), $user->name());
    } else {
      $tools->redirect("nonfareilfurbo.php");
    }
  }
} elseif($tools->validate_form_data('$post-mod', true, "modifica")) {
  bdump("per poco...");
  if($tools->validate_form_data(['$post-id', '$post-data', '$post-codice', '$post-uscita', '$post-rientro', '$post-capo', '$post-luogo', '$post-note', '$post-tipo', '$post-token'])) {
    if($_POST["token"] == $_SESSION['token']){
      bdump("modifico intervento");
    } else {
      $tools->redirect("nonfareilfurbo.php");
    }
  }
} elseif($tools->validate_form_data('$post-mod', true, "elimina")) {
  bdump("rimuovo intervento");
  if($tools->validate_form_data(['$post-id', '$post-token'])) {
    if($_POST["token"] == $_SESSION['token']){
      bdump("rimuovo intervento");
    } else {
      $tools->redirect("nonfareilfurbo.php");
    }
  }
} else {
  $length = 32;
  unset($_SESSION['token']);
  $_SESSION['token'] = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $length) . "-bfwp64GGbdm";
  // 1 hour = 60 seconds * 60 minutes = 3600
  $_SESSION['token-expire'] = time() + 3600;
  $personale = $database->exec("SELECT * FROM `%PREFIX%_profiles` ORDER BY name ASC", true); // Pesco i dati della table e li ordino in base al name
  $tipologie = $database->exec("SELECT name FROM `%PREFIX%_tipo` ORDER BY name ASC", true); // Pesco le tipologie della table
  $modalità = (isset($_GET["add"])) ? "add" : ((isset($_GET["modifica"])) ? "modifica" : ((isset($_GET["elimina"])) ? "elimina" : "add"));
  bdump($modalità, "modalità");
  bdump($tipologie, "tipologie");
  bdump($personale, "personale");
  $id = "";
  if(isset($_GET["id"])){
      $id = $_GET["id"];
      bdump($database->exists("interventi", $id));
  }
  if($modalità=="modifica" || $modalità=="elimina"){
      if(empty($id)){
          $tools->redirect("nonfareilfurbo.php");
      } elseif (!$database->exists("interventi", $id)){
          $tools->redirect("nonfareilfurbo.php");
      }
  }
  loadtemplate('modifica_intervento.html', ['intervento' => array('id' => $id, 'token' => $_SESSION['token'], 'modalità' => $modalità, 'personale' => $personale, 'tipologie' => $tipologie), 'titolo' => ucfirst($modalità) . ' intervento', 'owner' => 'VVF Darfo', 'urlsoftware' => '', 'user' => $user->info()]);
  bdump($_SESSION['token'], "token");
}
?>