<?php
include_once("../../core.php");
init_class();
$user->requirelogin();

$risultato = $database->exec("SELECT * FROM `%PREFIX%_profiles` ORDER BY available DESC, chief DESC, services ASC, availability_minutes ASC, name ASC;", true);

$hidden = $user->hidden();
?>
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
     if(!in_array($row['name'], $hidden) && ($row['hidden'] == 0 && $row['disabled'] == 0)){
      echo "<tr><td>";
      if ($row['chief'] == 1) {echo "<img src='./resources/images/red_helmet.png' width='20px'>   ";} else{echo "<img src='./resources/images/black_helmet.png' width='20px'>   ";}
      if((time()-$row["online_time"])<=30){
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
