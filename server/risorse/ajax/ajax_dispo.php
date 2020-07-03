<?php
$start = true;
$minuti = 5;
include_once "../../core.php";
init_class();
$user->requirelogin();

function arraynum(){
global $database;
$risultato = $database->exec("SELECT * FROM `%PREFIX%_profiles` ORDER BY available DESC, caposquadra DESC, services ASC, name ASC", true); // Pesco i dati della table e li ordino in base alla disponibilità
$incremento = array();
$minuti_dispo_old = array();
foreach($risultato as $row){
    if($row['available'] == "1"){
        $incremento[] = $row['name'];
        $minuti_dispo_old[] = $row['minuti_dispo'];
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
