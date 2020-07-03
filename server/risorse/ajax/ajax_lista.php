<?php
include_once("../../core.php");
init_class();
$user->requirelogin();

$risultato = $database->exec("SELECT * FROM `%PREFIX%_profiles` ORDER BY available DESC, caposquadra DESC, services ASC, minuti_dispo ASC, name ASC;", true);

$hidden = $user->hidden();
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
     <th><?php t("Name"); ?></th>
     <th><?php t("Available"); ?></th>
     <?php
   foreach($risultato as $row){
     if(!in_array($row['name'], $hidden)){
      echo "<tr><td>";
      if ($row['caposquadra'] == 1) {echo "<img src='./risorse/images/cascoRosso.png' width='20px'>   ";} else{echo "<img src='./risorse/images/cascoNero.png' width='20px'>   ";}
      if($row['online'] == 1){
          echo "<u>".$user->nameById($row["id"])."</u></td><td>";
      } else {
          echo "".$user->nameById($row["id"])."</td><td>";
      }
      if ($row['available'] == 1) {echo "<i class='fa fa-check' style='color:green'></i>";} else{echo "<i class='fa fa-times'  style='color:red'></i>";};
      echo  "</td></tr>";
      }
     }
     ?>
   </table>
</div>
