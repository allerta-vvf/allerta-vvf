<?php
require_once 'ui.php';
if($tools->validate_form_data('$post-mod', true, "add")) {
  if($tools->validate_form_data(['$post-data', '$post-codice', '$post-uscita', '$post-rientro', '$post-capo', '$post-luogo', '$post-note', '$post-tipo', '$post-token'])) {
    if($_POST["token"] == $_SESSION['token']){
      bdump("adding service");
      $database->add_service($_POST["data"], $_POST["codice"], $_POST["uscita"], $_POST["rientro"], $_POST["capo"][0], $tools->extract_unique($_POST["autisti"]), $tools->extract_unique($_POST["personale"]), $_POST["luogo"], $_POST["note"], $_POST["tipo"], $tools->extract_unique([$_POST["capo"],$_POST["autisti"],$_POST["personale"]]), $user->name());
      $tools->redirect("services.php");
    } else {
      $tools->redirect("accessdenied.php");
    }
  }
} elseif($tools->validate_form_data('$post-mod', true, "edit")) {
  if($tools->validate_form_data(['$post-id', '$post-data', '$post-codice', '$post-uscita', '$post-rientro', '$post-capo', '$post-luogo', '$post-note', '$post-tipo', '$post-token'])) {
    if($_POST["token"] == $_SESSION['token']){
      bdump($_POST);
      bdump("editing service");
      $database->change_service($_POST["id"], $_POST["data"], $_POST["codice"], $_POST["uscita"], $_POST["rientro"], $_POST["capo"][0], $tools->extract_unique($_POST["autisti"]), $tools->extract_unique($_POST["personale"]), $_POST["luogo"], $_POST["note"], $_POST["tipo"], $tools->extract_unique([$_POST["capo"],$_POST["autisti"],$_POST["personale"]]), $user->name());
      $tools->redirect("services.php");
    } else {
      $tools->redirect("accessdenied.php");
    }
  }
} elseif($tools->validate_form_data('$post-mod', true, "delete")) {
  bdump("removing service");
  if($tools->validate_form_data(['$post-id', '$post-increment', '$post-token'])) {
    if($_POST["token"] == $_SESSION['token']){
      bdump("removing service");
      $database->remove_service($_POST["id"], $_POST["increment"]);
      $tools->redirect("services.php");
    } else {
      $tools->redirect("accessdenied.php");
    }
  }
} else {
  if(isset($_GET["add"])||isset($_GET["edit"])||isset($_GET["delete"])||isset($_GET["mod"])){
    $_SESSION["token"] = bin2hex(random_bytes(64));
  }
  $personale = $database->exec("SELECT * FROM `%PREFIX%_profiles` ORDER BY name ASC;", true); // Pesco i dati della table e li ordino in base al name
  $tipologie = $database->exec("SELECT `name` FROM `%PREFIX%_tipo` ORDER BY name ASC", true); // Pesco le tipologie della table
  $modalità = (isset($_GET["add"])) ? "add" : ((isset($_GET["edit"])) ? "edit" : ((isset($_GET["delete"])) ? "delete" : "add"));
  bdump($modalità, "modalità");
  bdump($tipologie, "tipologie");
  bdump($personale, "personale");
  $id = "";
  if(isset($_GET["id"])){
      $id = $_GET["id"];
      bdump($database->exists("services", $id));
      $values = $database->exec("SELECT * FROM `%PREFIX%_services` WHERE `id` = :id", true, [":id" => $id])[0]; // Pesco le tipologie della table
      bdump($values);
  } else {
      $values = [];
  }
  if(isset($_GET["increment"])){
      $increment = $_GET["increment"];
  } else {
      $increment = "";
  }
  if($modalità=="edit" || $modalità=="delete"){
      if(empty($id)){
          $tools->redirect("accessdenied.php");
      } elseif (!$database->exists("services", $id)){
          $tools->redirect("accessdenied.php");
      }
  }
  loadtemplate('edit_service.html', ['service' => ['id' => $id, 'token' => $_SESSION['token'], 'modalità' => $modalità, 'personale' => $personale, 'tipologie' => $tipologie], 'values' => $values, 'increment' => $increment, 'title' => ucfirst($modalità) . ' '.ucfirst(t("service",false))]);
  bdump($_SESSION['token'], "token");
}
?>