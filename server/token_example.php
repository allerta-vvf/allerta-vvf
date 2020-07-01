<?php
require("ui.php");

if(isset($_GET["mod"])){
    $_SESSION["token"] = bin2hex(random_bytes(64));
?>
<form>
    <input type="hidden" name="token" value="<?php echo $_SESSION["token"]; ?>"></input>
    <input type="submit"></input>
</form>
<?php
    bdump($_SESSION["token"]);
} else if (isset($_GET["token"])) {
    echo $_SESSION["token"] . "<br>";
    echo $_GET["token"];
}