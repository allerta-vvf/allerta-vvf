<?php
require_once 'ui.php';
if($tools->validate_form_data('$post-mod', true, "add")) {
  if($tools->validate_form_data(['$post-mail', '$post-name', '$post-username', '$post-password', '$post-birthday', '$post-token'])) {
    if($_POST["token"] == $_SESSION['token']){
      bdump("adding user");
      bdump($_POST);
      $capo = isset($_POST["capo"]) ? 1 : 0;
      $autista = isset($_POST["autista"]) ? 1 : 0;
      $hidden = isset($_POST["visible"]) ? 0 : 1;
      $disabled = isset($_POST["enabled"]) ? 0 : 1;
      $user->add_user($_POST["mail"], $_POST["name"], $_POST["username"], $_POST["password"], $_POST["birthday"], $capo, $autista, $hidden, $disabled, $user->name());
      $tools->redirect("lista.php");
    } else {
      $tools->redirect("nonfareilfurbo.php");
    }
  }
/*} elseif($tools->validate_form_data('$post-mod', true, "edit")) {
  if($tools->validate_form_data(['$post-id', '$post-data', '$post-codice', '$post-uscita', '$post-rientro', '$post-capo', '$post-luogo', '$post-note', '$post-tipo', '$post-token'])) {
    if($_POST["token"] == $_SESSION['token']){
      bdump($_POST);
      bdump("editing service");
      $database->change_service($_POST["id"], $_POST["data"], $_POST["codice"], $_POST["uscita"], $_POST["rientro"], $_POST["capo"], $tools->extract_unique($_POST["autisti"]), $tools->extract_unique($_POST["personale"]), $_POST["luogo"], $_POST["note"], $_POST["tipo"], $tools->extract_unique([$_POST["capo"],$_POST["autisti"],$_POST["personale"]]), $user->name());
      $tools->redirect("services.php");
    } else {
      $tools->redirect("nonfareilfurbo.php");
    }
  }
*/} elseif($tools->validate_form_data('$post-mod', true, "delete")) {
  bdump("removing service");
  if($tools->validate_form_data(['$post-id', '$post-token'])) {
    if($_POST["token"] == $_SESSION['token']){
      bdump("removing user");
      $user->remove_user($_POST["id"]);
      $tools->redirect("lista.php");
    } else {
      $tools->redirect("nonfareilfurbo.php");
    }
  }
} else {
  if(isset($_GET["add"])||isset($_GET["edit"])||isset($_GET["delete"])||isset($_GET["mod"])){
    $_SESSION["token"] = bin2hex(random_bytes(64));
  }
  $modalità = (isset($_GET["add"])) ? "add" : ((isset($_GET["edit"])) ? "edit" : ((isset($_GET["delete"])) ? "delete" : "add"));
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
  if($modalità=="edit" || $modalità=="delete"){
      if(empty($id)){
          $tools->redirect("nonfareilfurbo.php");
      } elseif (!$database->exists("profiles", $id)){
          $tools->redirect("nonfareilfurbo.php");
      }
  }
  loadtemplate('edit_user.html', ['id' => $id, 'token' => $_SESSION["token"], 'modalità' => $modalità, 'values' => $values, 'titolo' => ucfirst($modalità) . ' '.ucfirst(t("user",false))]);
  bdump($_SESSION['token'], "token");
}
?>