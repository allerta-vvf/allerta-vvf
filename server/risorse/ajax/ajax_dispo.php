<?php
$start = true;
$minutes = 5;
include_once "../../core.php";
init_class();
$user->requirelogin();

function arraynum(){
global $database;
$risultato = $database->exec("SELECT * FROM `%PREFIX%_profiles` ORDER BY available DESC, caposquadra DESC, services ASC, name ASC", true);
$incremento = array();
$availability_minutes_old = array();
foreach($risultato as $row){
    if($row['available'] == "1"){
        $incremento[] = $row['name'];
        $availability_minutes_old[] = $row['availability_minutes'];
    }
}

return $incremento;
}

if(!isset($_GET['name'])){
print_r(arraynum());
} else {
    if(isset($_GET['name'])){
        $arr = arraynum();
        $name = str_replace("_", " ", $_GET['name']);
        if(in_array($name, $arr)){
            echo "si";
        } else {
            echo "no";
        }
    }
}
?>
