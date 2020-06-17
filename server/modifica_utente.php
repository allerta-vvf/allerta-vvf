<?php
require_once 'ui.php';
if($tools->validate_form_data('$post-mod', true, "add")) {
  if($tools->validate_form_data(['$post-mail', '$post-name', '$post-username', '$post-password', '$post-birthday', '$post-token'])) {
    if($_POST["token"] == $_SESSION['token']){
      bdump("aggiungo utente");
      bdump($_POST);
      $capo = isset($_POST["capo"]) ? 1 : 0;
      $autista = isset($_POST["autista"]) ? 1 : 0;
      $hidden = isset($_POST["visible"]) ? 0 : 1;
      $disabled = isset($_POST["enabled"]) ? 0 : 1;
      $user->add_utente($_POST["mail"], $_POST["name"], $_POST["username"], $_POST["password"], $_POST["birthday"], $capo, $autista, $hidden, $disabled, $user->name());
      $tools->redirect("interventi.php");
    } else {
      $tools->redirect("nonfareilfurbo.php");
    }
  }
/*} elseif($tools->validate_form_data('$post-mod', true, "modifica")) {
  if($tools->validate_form_data(['$post-id', '$post-data', '$post-codice', '$post-uscita', '$post-rientro', '$post-capo', '$post-luogo', '$post-note', '$post-tipo', '$post-token'])) {
    if($_POST["token"] == $_SESSION['token']){
      bdump($_POST);
      bdump("modifico intervento");
      $database->change_intervento($_POST["id"], $_POST["data"], $_POST["codice"], $_POST["uscita"], $_POST["rientro"], $_POST["capo"], $tools->extract_unique($_POST["autisti"]), $tools->extract_unique($_POST["personale"]), $_POST["luogo"], $_POST["note"], $_POST["tipo"], $tools->extract_unique([$_POST["capo"],$_POST["autisti"],$_POST["personale"]]), $user->name());
      $tools->redirect("interventi.php");
    } else {
      $tools->redirect("nonfareilfurbo.php");
    }
  }
} elseif($tools->validate_form_data('$post-mod', true, "elimina")) {
  bdump("rimuovo intervento");
  if($tools->validate_form_data(['$post-id', '$post-incrementa', '$post-token'])) {
    if($_POST["token"] == $_SESSION['token']){
      bdump("rimuovo intervento");
      $database->remove_intervento($_POST["id"], $_POST["incrementa"]);
      $tools->redirect("interventi.php");
    } else {
      $tools->redirect("nonfareilfurbo.php");
    }
  }
*/} else {
  if(!isset($_GET["_tracy_bar"])){
    $length = 32;
  unset($_SESSION['token']);
  bdump("codice");
  $_SESSION['token'] = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $length) . "-bfwp64GGbdm";
  // 1 hour = 60 seconds * 60 minutes = 3600
  $_SESSION['token-expire'] = time() + 3600;
  }
  $modalità = (isset($_GET["add"])) ? "add" : ((isset($_GET["modifica"])) ? "modifica" : ((isset($_GET["elimina"])) ? "elimina" : "add"));
  bdump($modalità, "modalità");
  $id = "";
  if(isset($_GET["id"])){
      $id = $_GET["id"];
      bdump($database->exists("profiles", $id));
      $values = $database->exec("SELECT * FROM `%PREFIX%_profiles` WHERE `id` = :id", true, [":id" => $id])[0]; // Pesco le tipologie della table
      bdump($values);
  } else {
      $values = [];
  }
  if($modalità=="modifica" || $modalità=="elimina"){
      if(empty($id)){
          $tools->redirect("nonfareilfurbo.php");
      } elseif (!$database->exists("profiles", $id)){
          $tools->redirect("nonfareilfurbo.php");
      }
  }
  loadtemplate('modifica_utente.html', ['id' => $id, 'token' => $_SESSION['token'], 'modalità' => $modalità, 'values' => $values, 'titolo' => ucfirst($modalità) . ' utente']);
  bdump($_SESSION['token'], "token");
}
?>