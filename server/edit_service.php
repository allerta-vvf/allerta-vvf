<?php
require_once 'ui.php';
function debug(){
    echo("<pre>"); var_dump($_POST); echo("</pre>"); exit();
}
if($tools->validate_form("mod", "add")) {
    if($tools->validate_form(['date', 'code', 'beginning', 'end', 'place', 'notes', 'type', 'token'])) {
        if($_POST["token"] == $_SESSION['token']) {
            bdump("adding service");
            $database->add_service($_POST["date"], $_POST["code"], $_POST["beginning"], $_POST["end"], $_POST["chief"][0], $tools->extract_unique($_POST["drivers"]), $tools->extract_unique($_POST["crew"]), $_POST["place"], $_POST["notes"], $_POST["type"], $tools->extract_unique([$_POST["chief"],$_POST["drivers"],$_POST["crew"]]), $user->name());
            $tools->redirect("services.php");
        } else {
            debug(); //TODO: remove debug info
        }
    } else {
        debug();
    }
} elseif($tools->validate_form("mod", "edit")) {
    if($tools->validate_form(['id', 'date', 'code', 'beginning', 'end', 'place', 'notes', 'type', 'token'])) {
        if($_POST["token"] == $_SESSION['token']) {
            bdump($_POST);
            bdump("editing service");
            $database->change_service($_POST["id"], $_POST["date"], $_POST["code"], $_POST["beginning"], $_POST["end"], $_POST["chief"][0], $tools->extract_unique($_POST["drivers"]), $tools->extract_unique($_POST["crew"]), $_POST["place"], $_POST["notes"], $_POST["type"], $tools->extract_unique([$_POST["chief"],$_POST["drivers"],$_POST["crew"]]), $user->name());
            $tools->redirect("services.php");
        } else {
            debug();
        }
    } else {
        debug();
    }
} elseif($tools->validate_form("mod", "delete")) {
    bdump("removing service");
    if($tools->validate_form(['id', 'increment', 'token'])) {
        if($_POST["token"] == $_SESSION['token']) {
            bdump("removing service");
            $database->remove_service($_POST["id"], $_POST["increment"]);
            $tools->redirect("services.php");
        } else {
            echo("1");
            debug();
        }
    } else {
        echo("2");
        debug();
    }
} else {
    if(isset($_GET["add"])||isset($_GET["edit"])||isset($_GET["delete"])||isset($_GET["mod"])) {
        $_SESSION["token"] = bin2hex(random_bytes(64));
    }
    $crew = $database->exec("SELECT * FROM `%PREFIX%_profiles` ORDER BY name ASC;", true);
    $types = $database->exec("SELECT `name` FROM `%PREFIX%_type` ORDER BY name ASC", true);
    $modalità = (isset($_GET["add"])) ? "add" : ((isset($_GET["edit"])) ? "edit" : ((isset($_GET["delete"])) ? "delete" : "add"));
    bdump($modalità, "modalità");
    bdump($types, "types");
    bdump($crew, "crew");
    $id = "";
    if(isset($_GET["id"])) {
        $id = $_GET["id"];
        bdump($database->exists("services", $id));
        $values = $database->exec("SELECT * FROM `%PREFIX%_services` WHERE `id` = :id", true, [":id" => $id])[0];
        bdump($values);
    } else {
        $values = [];
    }
    if(isset($_GET["increment"])) {
        $increment = $_GET["increment"];
    } else {
        $increment = "";
    }
    if($modalità=="edit" || $modalità=="delete") {
        if(empty($id)) {
            echo("<pre>"); var_dump($_POST); echo("</pre>");
        } elseif (!$database->exists("services", $id)) {
            echo("<pre>"); var_dump($_POST); echo("</pre>");
        }
    }
    loadtemplate('edit_service.html', ['service' => ['id' => $id, 'token' => $_SESSION['token'], 'modalità' => $modalità, 'crew' => $crew, 'types' => $types], 'values' => $values, 'increment' => $increment, 'title' => ucfirst($modalità) . ' '.ucfirst(t("service", false))]);
    bdump($_SESSION['token'], "token");
}
?>
