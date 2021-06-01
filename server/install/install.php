<?php
if (file_exists("installHelper.php")) {
    try {
        include 'installHelper.php';
    } catch (Exception $e) {
        die("Please re-download Allerta. Installation corrupted (".$e.")");
    }
} else {
    die("Please re-download Allerta. Installation corrupted");
}

if (!file_exists("runInstall.php")) {
    t("Already installed");
    exit();
}

$populated = false;
$userPopulated = false;
if (file_exists("../config.php")) {
    try {
        include '../config.php';
        $dbnameValue = DB_NAME;
        $unameValue = DB_USER;
        $pwdValue = DB_PASSWORD;
        $dbhostValue = DB_HOST;
        $prefixValue = DB_PREFIX;
        if(checkConnection($dbhostValue, $unameValue, $pwdValue, $dbnameValue, true)) {
            $configOk = true;
            try{
                $db = \Delight\Db\PdoDatabase::fromDsn(
                    new \Delight\Db\PdoDsn(
                        "mysql:host=$dbhostValue;dbname=$dbnameValue",
                        $unameValue,
                        $pwdValue
                    )
                );
                try{
                    $populated = !is_null($db->select("SELECT * FROM `".DB_PREFIX."_dbversion`"));
                } catch (Delight\Db\Throwable\TableNotFoundError $e){
                    $populated = false;
                }
                try{
                    $userPopulated = !is_null($db->select("SELECT * FROM `".DB_PREFIX."_users`"));
                } catch (Delight\Db\Throwable\TableNotFoundError $e){
                    $userPopulated = false;
                }
            } catch (Exception $e){
                $populated = false;
                $userPopulated = false;
            }
        }
    } catch (Exception $e) {
        $dbnameValue = "allerta";
        $unameValue = t("user", false);
        $pwdValue = t("password", false);
        $dbhostValue = "127.0.0.1";
        $prefixValue = "allerta01";
        $configOk = false;
    }
} else {
    $dbnameValue = "allerta";
    $unameValue = t("user", false);
    $pwdValue = t("password", false);
    $dbhostValue = "127.0.0.1";
    $prefixValue = "allerta01";
    $configOk = false;
}

if(!is_cli()) {
    $loader = new \Twig\Loader\FilesystemLoader(".");
    $twig = new \Twig\Environment($loader);

    $filter_translate = new \Twig\TwigFilter(
        't', function ($string) {
            return t($string, false);
        }
    );
    $twig->addFilter($filter_translate);

    if(!isset($_GET["old"])){
        $template = $twig->load("install.html");
        echo($template->render([
            "step" => isset($_POST["step"]) ? $_POST["step"] : 1,
            "configOk" => $configOk,
            "populated" => $populated,
            "userPopulated" => $userPopulated,
            "dbConfig" => [
                "dbname" => $dbnameValue,
                "user" => $unameValue,
                "pwd" => $pwdValue,
                "host" => $dbhostValue,
                "prefix" => $prefixValue
            ]
        ]));
    }

    if (in_array("3", $_POST)) {
        checkConnection($_POST["dbhost"], $_POST["uname"], $_POST["pwd"], $_POST["dbname"]);
        generateConfig($_POST["dbhost"], $_POST["uname"], $_POST["pwd"], $_POST["dbname"], $_POST["prefix"]);
    } else if ($configOk && !$populated) {
        initDB();
    } else if (in_array("5", $_POST)) {
        initOptions($_POST["user_name"], isset($_POST["admin_visible"]), isset($_POST["developer"]), $_POST["admin_password"], $_POST["admin_email"], $_POST["owner"]);
        finalInstallationHelperStep();
    }
} else {
    run_cli();
}
?>