<?php
require_once 'ui.php';
if($tools->validate_form_data('$post-mod', true, "add")) {
  if($tools->validate_form_data(['$post-data', '$post-name', '$post-start_time', '$post-end_time', '$post-capo', '$post-luogo', '$post-note', '$post-token'])) {
    if($_POST["token"] == $_SESSION['token']){
      bdump("aggiungo training");
      $database->add_training($_POST["data"], $_POST["name"], $_POST["start_time"], $_POST["end_time"], $_POST["capo"], $tools->extract_unique($_POST["personale"]), $_POST["luogo"], $_POST["note"], $tools->extract_unique([$_POST["capo"],$_POST["personale"]]), $user->name());
      $tools->redirect("trainings.php");
    } else {
      $tools->redirect("nonfareilfurbo.php");
    }
  }
} elseif($tools->validate_form_data('$post-mod', true, "modifica")) {
  if($tools->validate_form_data(['$post-id', '$post-data', '$post-name', '$post-start_time', '$post-end_time', '$post-capo', '$post-luogo', '$post-note', '$post-token'])) {
    if($_POST["token"] == $_SESSION['token']){
      bdump($_POST);
      bdump("modifico training");
      $database->change_training($_POST["id"], $_POST["data"], $_POST["name"], $_POST["start_time"], $_POST["end_time"], $_POST["capo"], $tools->extract_unique($_POST["personale"]), $_POST["luogo"], $_POST["note"], $tools->extract_unique([$_POST["capo"],$_POST["personale"]]), $user->name());
      $tools->redirect("trainings.php");
    } else {
      $tools->redirect("nonfareilfurbo.php");
    }
  }
} elseif($tools->validate_form_data('$post-mod', true, "elimina")) {
  bdump("rimuovo training");
  if($tools->validate_form_data(['$post-id', '$post-incrementa', '$post-token'])) {
    if($_POST["token"] == $_SESSION['token']){
      bdump("rimuovo training");
      $database->remove_training($_POST["id"], $_POST["incrementa"]);
      $tools->redirect("trainings.php");
    } else {
      $tools->redirect("nonfareilfurbo.php");
    }
  }
} else {
  if(isset($_GET["add"])||isset($_GET["modifica"])||isset($_GET["elimina"])||isset($_GET["mod"])){
    $_SESSION["token"] = bin2hex(random_bytes(64));
  }
  $personale = $database->exec("SELECT * FROM `%PREFIX%_profiles` ORDER BY name ASC;", true); // Pesco i dati della table e li ordino in base al name
  $modalità = (isset($_GET["add"])) ? "add" : ((isset($_GET["modifica"])) ? "modifica" : ((isset($_GET["elimina"])) ? "elimina" : "add"));
  bdump($modalità, "modalità");
  bdump($personale, "personale");
  $id = "";
  if(isset($_GET["id"])){
      $id = $_GET["id"];
      bdump($database->exists("trainings", $id));
      $values = $database->exec("SELECT * FROM `%PREFIX%_trainings` WHERE `id` = :id", true, [":id" => $id])[0]; // Pesco le tipologie della table
      bdump($values);
  } else {
      $values = [];
  }
  if(isset($_GET["incrementa"])){
      $incrementa = $_GET["incrementa"];
  } else {
      $incrementa = "";
  }
  if($modalità=="modifica" || $modalità=="elimina"){
      if(empty($id)){
          $tools->redirect("nonfareilfurbo.php");
      } elseif (!$database->exists("trainings", $id)){
          //$tools->redirect("nonfareilfurbo.php");
      }
  }
  loadtemplate('modifica_training.html', ['training' => ['id' => $id, 'token' => $_SESSION['token'], 'modalità' => $modalità, 'personale' => $personale], 'values' => $values, 'incrementa' => $incrementa, 'titolo' => ucfirst($modalità) . ' training']);
  bdump($_SESSION['token'], "token");
}
?>