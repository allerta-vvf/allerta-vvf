<?php
require_once 'ui.php';
function debug(){
    echo("<pre>"); var_dump($_POST); echo("</pre>"); exit();
}
if($tools->validate_form("mod", "add")) {
    if($tools->validate_form(['date', 'name', 'start_time', 'end_time', 'place', 'notes', 'token'])) {
        if($_POST["token"] == $_SESSION['token']) {
            bdump("adding training");
            $crud->add_training($_POST["date"], $_POST["name"], $_POST["start_time"], $_POST["end_time"], $_POST["chief"][0], $tools->extract_unique($_POST["crew"]), $_POST["place"], $_POST["notes"], $tools->extract_unique([$_POST["chief"],$_POST["crew"]]), $user->name());
            $tools->redirect("trainings.php");
        } else {
            debug(); //TODO: remove debug info
        }
    } else {
        debug();
    }
} elseif($tools->validate_form("mod", "edit")) {
    if($tools->validate_form(['id', 'date', 'name', 'start_time', 'end_time', 'chief', 'place', 'notes', 'token'])) {
        if($_POST["token"] == $_SESSION['token']) {
            bdump($_POST);
            bdump("editing training");
            $crud->edit_training($_POST["id"], $_POST["date"], $_POST["name"], $_POST["start_time"], $_POST["end_time"], $_POST["chief"][0], $tools->extract_unique($_POST["crew"]), $_POST["place"], $_POST["notes"], $tools->extract_unique([$_POST["chief"],$_POST["crew"]]), $user->name());
            $tools->redirect("trainings.php");
        } else {
            debug();
        }
    } else {
        debug();
    }
} elseif($tools->validate_form("mod", "delete")) {
    bdump("removing training");
    if($tools->validate_form(['id', 'token'])) {
        if($_POST["token"] == $_SESSION['token']) {
            bdump("removing training");
            $crud->remove_training($_POST["id"]);
            $tools->redirect("trainings.php");
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
    $crew = $database->exec("SELECT * FROM `%PREFIX%_profiles` ORDER BY name ASC;", true);
    $modalità = (isset($_GET["add"])) ? "add" : ((isset($_GET["edit"])) ? "edit" : ((isset($_GET["delete"])) ? "delete" : "add"));
    bdump($modalità, "modalità");
    bdump($crew, "crew");
    $id = "";
    if(isset($_GET["id"])) {
        $id = $_GET["id"];
        bdump($database->exists("trainings", $id));
        $values = $database->exec("SELECT * FROM `%PREFIX%_trainings` WHERE `id` = :id", true, [":id" => $id])[0];
        bdump($values);
    } else {
        $values = [];
    }
    if($modalità=="edit" || $modalità=="delete") {
        if(empty($id)) {
            $tools->redirect("accessdenied.php");
        } elseif (!$database->exists("trainings", $id)) {
            //$tools->redirect("accessdenied.php");
        }
    }
    loadtemplate('edit_training.html', ['training' => ['id' => $id, 'token' => $_SESSION['token'], 'modalità' => $modalità, 'crew' => $crew], 'values' => $values, 'title' => ucfirst($modalità) . ' '.ucfirst(t("training", false))]);
    bdump($_SESSION['token'], "token");
}
?>
