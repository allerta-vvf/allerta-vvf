<?php
require_once 'ui.php';
function debug(){
    echo("<pre>"); var_dump($_POST); echo("</pre>"); exit();
}
if($tools->validate_form("mod", "add")) {
    if($tools->validate_form(['mail', 'name', 'username', 'password', 'birthday', 'token'])) {
        if($_POST["token"] == $_SESSION['token']) {
            bdump("adding user");
            bdump($_POST);
            $chief = isset($_POST["chief"]) ? 1 : 0;
            $driver = isset($_POST["driver"]) ? 1 : 0;
            $hidden = isset($_POST["visible"]) ? 0 : 1;
            $disabled = isset($_POST["enabled"]) ? 0 : 1;
            $user->add_user($_POST["mail"], $_POST["name"], $_POST["username"], $_POST["password"], $_POST["birthday"], $chief, $driver, $hidden, $disabled, $user->name());
            $tools->redirect("list.php");
        } else {
            debug();
        }
    } else {
        debug();
    }
/*} elseif($tools->validate_form("mod", "edit")) {
    if($tools->validate_form(['mail', 'name', 'username', 'password', 'birthday', 'token'])) {
    if($_POST["token"] == $_SESSION['token']){
      bdump($_POST);
      bdump("editing service");
      $database->change_service($_POST["id"], $_POST["date"], $_POST["code"], $_POST["beginning"], $_POST["end"], $_POST["chief"], $tools->extract_unique($_POST["drivers"]), $tools->extract_unique($_POST["crew"]), $_POST["place"], $_POST["notes"], $_POST["type"], $tools->extract_unique([$_POST["chief"],$_POST["drivers"],$_POST["crew"]]), $user->name());
      $tools->redirect("services.php");
    } else {
      $tools->redirect("accessdenied.php");
    }
    }
    */
} elseif($tools->validate_form("mod", "delete")) {
    bdump("removing service");
    if($tools->validate_form(['id', 'token'])) {
        if($_POST["token"] == $_SESSION['token']) {
            bdump("removing user");
            $user->remove_user($_POST["id"]);
            $tools->redirect("list.php");
        } else {
            debug();
        }
    } else {
        debug();
    }
} else {
    if(isset($_GET["add"])||isset($_GET["edit"])||isset($_GET["delete"])||isset($_GET["mod"])) {
        $_SESSION["token"] = bin2hex(random_bytes(64));
    }
        $modalità = (isset($_GET["add"])) ? "add" : ((isset($_GET["edit"])) ? "edit" : ((isset($_GET["delete"])) ? "delete" : "add"));
        bdump($modalità, "modalità");
        $id = "";
    if(isset($_GET["id"])) {
        $id = $_GET["id"];
        bdump($database->exists("profiles", $id));
        $values = $database->exec("SELECT * FROM `%PREFIX%_profiles` WHERE `id` = :id", true, [":id" => $id])[0]; // Pesco le types della table
        bdump($values);
    } else {
        $values = [];
    }
    if($modalità=="edit" || $modalità=="delete") {
        if(empty($id)) {
            $tools->redirect("accessdenied.php");
        } elseif (!$database->exists("profiles", $id)) {
            $tools->redirect("accessdenied.php");
        }
    }
        loadtemplate('edit_user.html', ['id' => $id, 'token' => $_SESSION["token"], 'modalità' => $modalità, 'values' => $values, 'title' => ucfirst($modalità) . ' '.ucfirst(t("user", false))]);
        bdump($_SESSION['token'], "token");
}
?>