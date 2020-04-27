<?php
include_once("../../secure.php");
init_class();
$utente->richiedilogin();

$risultato = $database->esegui("SELECT * FROM vigili ORDER BY disponibile DESC, caposquadra DESC, interventi ASC, minuti_dispo ASC, nome ASC", true);

$whitelist = $utente->whitelist();
?>
<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<style>
th, td {
    border: 1px solid grey;
    border-collapse: collapse;
 padding: 5px;
}

#href {
 outline: none;
 cursor: pointer;
 text-align: center;
 text-decoration: none;
 font: bold 12px Arial, Helvetica, sans-serif;
 color: #fff;
 padding: 10px 20px;
 border: solid 1px #0076a3;
 background: #0095cd;
}

 table {
   box-shadow: 2px 2px 25px rgba(0,0,0,0.5);
    border-radius: 15px;
  margin: auto;
 }


</style>
<div style="overflow-x:auto;">
<table style="width: 90%; text-align:center;">
  <tr>
    <th>Nome</th>
    <th>Disponibile</th>
    <th>Autista</th>
    <th>Chiama</th>
    <th>Scrivi</th>
    <th>Interventi</th>
    <th>Minuti Disponibilità</th>
    <th>Altro</th>
   <?php
   foreach($risultato as $row){
     if(!in_array($row['nome'], $whitelist) OR in_array($utente->nome(), $whitelist)){
       echo "<tr>
          <td>";
    $nome = $row["nome"];
    $disponibile = $row["disponibile"];
    if ($row['caposquadra'] == 1) {echo "<a onclick='AttivoAdmin(\"$nome\", \"$disponibile\");'><img src='./risorse/images/cascoRosso.png' width='20px'>   ";} else{echo "<a onclick='AttivoAdmin(\"$nome\", \"$disponibile\");'><img src='./risorse/images/cascoNero.png' width='20px'>   ";}
    if($row['online'] == 1){
        echo "<u>".$row["nome"]."</u></a></td><td><a onclick='AttivoAdmin(\"$nome\", \"$disponibile\");'>";
    } else {
        echo $row["nome"]."</a></td><td><a onclick='AttivoAdmin(\"$nome\", \"$disponibile\");'>";
    }
     if ($row['disponibile'] == 1) {echo "<i class='fa fa-check' style='color:green'></i>";} else{echo "<i class='fa fa-times'  style='color:red'></i>";};
       echo  "</a></td>
       <td>";
    if ($row['autista'] == 1) {echo "<img src='./risorse/images/volante.png' width='20px'>";} else{echo "";};
    echo "</td>
		  <td><a href='tel:+" . $row['telefono'] . "'><i class='fa fa-phone'></i></a></td><td>";

    if ($row['disponibile'] == 1) {echo "  <a href='https://api.whatsapp.com/send?phone=" . $row['telefono'] . "&text=ALLERTA IN CORSO.%20Mettiti%20in%20contatto%20con%20Fulvio'><i class='fa fa-whatsapp' style='color:green'></i></td>";} else{echo "";};

     $interventi = $row['interventi'];
     $minuti = $row['minuti_dispo'];
     $u = 'anagrafica.php?utente=' . str_replace(' ', '_', urldecode(strtolower($row["id"])));
     echo "<td>$interventi</td><td>$minuti</td><td><a href='$u'><p>Altri dettagli</p></a></td></tr>";
   }
}
    ?>
    </table>
</div>
