<?php
include_once 'core.php';

init_class();
$day = 19;
$ore = 1;


$start = true;
$minuti = 5;
setlocale(LC_TIME, 'ita', 'it_IT');
echo date('i') . " - " . date('H') . " - " . date("d") . "<br>";

function resetminuti(){
    global $profiles_tot;
    global $database;
    $sql = "SELECT * FROM %PREFIX%_profiles";
    $risultato = $database->exec($sql, true);
    $disp = array();
    foreach($risultato as $row){
        $disp[$row['id']] = $row['minuti_dispo'];
    }
    print("<br><pre>" . print_r($disp, true) . "</pre><br>");


    // 5.3:
    $list = implode(', ', array_map(
       function ($k, $v) { return "$k = $v;"; },
       array_keys($disp),
       array_values($disp)
    ));
    $a1 = implode(";", array_keys($disp));
    $a2 = implode(";", array_values($disp));
    $mese = strftime("%B");
    $anno = strftime("%Y");

    $sql = "INSERT INTO `%PREFIX%_minuti` (`id`, `mese`, `anno`, `list`, `a1`, `a2`) VALUES (NULL, '$mese', '$anno', '$list', '$a1', '$a2')";
    $risultato = $database->exec($sql, false, null, "UPDATE %PREFIX%_profiles SET minuti_dispo = '0' WHERE 1;");
}

//Per quando dovrò (forse) reinserire i valori in table o generare un array
function array_combine_($keys, $values){
    $result = array();
    foreach ($keys as $i => $k) {
        $result[$k][] = $values[$i];
    }
    array_walk($result, create_function('&$v', '$v = (count($v) == 1)? array_pop($v): $v;'));
    return    $result;
}

if($start && isset($_POST['cron']) && $_POST['cron'] == "cron_job-".$database->getOption("cron_job_code")){
if($start && isset($_POST['reset']) && $_POST['reset'] == "cron_job-".$database->getOption("cron_job_code")){
echo("reset");
resetminuti();
}

$sql = "SELECT * FROM %PREFIX%_profiles ORDER BY name ASC";
$risultato = $database->exec($sql, true);

$profiles_tot = array();
$incremento = array();
$minuti_dispo_old = array();
foreach($risultato as $row){
    $profiles_tot[] = $row['id'];
    if($row['available'] == "1"){
        $incremento[] = $row['id'];
        $minuti_dispo_old[] = $row['minuti_dispo'];
    }
}
print_r($incremento);

foreach($incremento as $key=>$id){
    $minuti_dispo = $minuti_dispo_old[$key] + $minuti;
    $sql = "UPDATE %PREFIX%_profiles SET minuti_dispo = '" . $minuti_dispo . "' WHERE id ='" . $id . "'";
    echo $sql;
    $risultato = $database->exec($sql, true);
}
$sql = "SELECT * FROM %PREFIX%_profiles ORDER BY available DESC, caposquadra DESC, services ASC, name ASC";
$risultato = $database->exec($sql, true);
$minuti_dispo = array();
foreach($risultato as $row){
    if($row['available'] == "1"){
        $minuti_dispo[] = $row['minuti_dispo'];
    }
}
echo "<br>";
print_r($minuti_dispo);
}
