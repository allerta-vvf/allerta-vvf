<?php
$day = 19;
$ore = 1;


$start = true;
$minuti = 5;
setlocale(LC_TIME, 'ita', 'it_IT');
echo date('i') . " - " . date('H') . " - " . date("d") . "<br>";

include_once 'core.php';

init_class();

function resetminuti(){
    global $vigili_tot;
    global $database;
    $sql = "SELECT * FROM %PREFIX%_vigili"; // Pesco i dati della tabella e li ordino in base alla disponibilità
    $risultato = $database->esegui($sql, true);
    $disp = array();
    foreach($risultato as $row){
        $disp[$row['nome']] = $row['minuti_dispo'];
    }
    print("<br><pre>" . print_r($disp, true) . "</pre><br>");


    // pre-5.3:
    $list = implode(', ', array_map(
       create_function('$k,$v', 'return "$k => $v";'),
       array_keys($disp),
       array_values($disp)
    ));
    $a1 = implode(" - ", array_keys($disp));
    $a2 = implode(" - ", array_values($disp));
    echo "<p style='color:red;'>" . $list . "</p><br><p style='color:green;'>" . $a1 . "</p><br><p style='color:blue;'>" . $a2 . "</p><br>";
    $mese = strftime("%B");
    $anno = strftime("%Y");
    echo $mese . " - " . $anno . "<br>";


    $sql = "INSERT INTO `%PREFIX%_minuti` (`id`, `mese`, `anno`, `list`, `a1`, `a2`) VALUES (NULL, '$mese', '$anno', '$list', '$a1', '$a2')"; // Pesco i dati della tabella e li ordino in base alla disponibilità
    $risultato = $database->esegui($sql);

    foreach($risultato as $row){
        $sql = "UPDATE %PREFIX%_vigili SET minuti_dispo = '0' WHERE nome ='" . $utente . "'";
        $risultato = $database->esegui($sql);
        echo "reset effettuato: " . $utente . "<br>";
    }

    if($risultato){
        echo <<<EOT
<img src='https://media1.tenor.com/images/768840dae0d91bbc9f215d9255af8170/tenor.gif?itemid=8706004'></img>
<img src='https://media1.tenor.com/images/4d41eec52c39344dd87e1022cc0eb98c/tenor.gif?itemid=4572479'></img>
<img src='https://thumbs.gfycat.com/FinishedSnarlingAfricanelephant-max-1mb.gif'></img>
EOT;
    }
}

//Per quando dovrò (forse) reinserire i valori in tabella o generare un array
function array_combine_($keys, $values){
    $result = array();
    foreach ($keys as $i => $k) {
        $result[$k][] = $values[$i];
    }
    array_walk($result, create_function('&$v', '$v = (count($v) == 1)? array_pop($v): $v;'));
    return    $result;
}
//print("<br><pre>" . print_r(array_combine_(explode(" - ", $a1), explode(" - ", $a2)), true) . "</pre><br>");


$sql = "SELECT * FROM %PREFIX%_vigili ORDER BY disponibile DESC, caposquadra DESC, interventi ASC, nome ASC"; // Pesco i dati della tabella e li ordino in base alla disponibilità
$risultato = $database->esegui($sql, true);

$vigili_tot = array();
$incremento = array();
$minuti_dispo_old = array();
foreach($risultato as $row){
    $vigili_tot[] = $row['nome'];
    if($row['disponibile'] == "1"){
        $incremento[] = $row['nome'];
        $minuti_dispo_old[] = $row['minuti_dispo'];
    }
}
print_r($incremento);

if($start && isset($_POST['cron']) && $_POST['cron'] == "cron-job"){
if($start && isset($_POST['reset']) && $_POST['reset'] == "cron-job"){
resetminuti();
}

foreach($incremento as $key=>$utente){
    $minuti_dispo = $minuti_dispo_old[$key] + $minuti;
    $sql = "UPDATE %PREFIX%_vigili SET minuti_dispo = '" . $minuti_dispo . "' WHERE nome ='" . $utente . "'";
    $risultato = $database->esegui($sql, true);
}
$sql = "SELECT * FROM %PREFIX%_vigili ORDER BY disponibile DESC, caposquadra DESC, interventi ASC, nome ASC"; // Pesco i dati della tabella e li ordino in base alla disponibilità
$risultato = $database->esegui($sql, true);
$minuti_dispo = array();
foreach($risultato as $row){
    if($row['disponibile'] == "1"){
        $minuti_dispo[] = $row['minuti_dispo'];
    }
}
echo "<br>";
print_r($minuti_dispo);
}
