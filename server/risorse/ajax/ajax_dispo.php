<?php
$start = true;
$minuti = 5;
include_once "../../secure.php";
init_class();
$utente->richiedilogin();

function arraynum(){
global $database;
$risultato = $database->esegui("SELECT * FROM vigili ORDER BY disponibile DESC, caposquadra DESC, interventi ASC, nome ASC", true); // Pesco i dati della tabella e li ordino in base alla disponibilità
$incremento = array();
$minuti_dispo_old = array();
foreach($risultato as $row){
    if($row['disponibile'] == "1"){
        $incremento[] = $row['nome'];
        $minuti_dispo_old[] = $row['minuti_dispo'];
    }
}

return $incremento;
}

if(!isset($_GET['nome'])){
print_r(arraynum());
} else {
    if(isset($_GET['nome'])){
        $arr = arraynum();
        $nome = str_replace("_", " ", $_GET['nome']);
        if(in_array($nome, $arr)){
            echo "si";
        } else {
            echo "no";
        }
    }
}
?>
