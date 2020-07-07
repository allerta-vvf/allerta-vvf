<?php
include_once("../../core.php");
init_class();
$user->requirelogin();

$impostazioni['edit'] = true;
$impostazioni['delete'] = true;

$risultato = $database->exec("SELECT * FROM `%PREFIX%_trainings` ORDER BY data DESC, inizio desc", true); // Pesco i dati della table e li ordino in base alla data
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>

#add {
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

   th, td {
    border: 1px solid grey;
    border-collapse: collapse;
    padding: 5px;
   }


   table {
    box-shadow: 0px 3px 15px rgba(0,0,0,0.5);
    border-radius: 5px;
    margin: auto;
   }

#new-search-area {
    width: 100%;
    clear: both;
    padding-top: 20px;
    padding-bottom: 20px;
}
#new-search-area input {
    width: 600px;
    font-size: 20px;
    padding: 5px;
    margin-right: 150px;
    margin-left: 80px;
}
  </style>
  <div style='margin: 20px 0;' class="mx-auto">
  <div style='margin: 2px auto' id="new-search-area"></div>
  <div class="table-responsive">
    <div style="overflow-x:auto;">
    <table id="trainings" cellspacing='0' class="display table table-striped table-bordered dt-responsive nowrap" style="width: 90%; text-align:center;">
    <thead>
    <tr>
     <th><?php t("Date"); ?></th>
     <th><?php t("Name"); ?></th>
     <th><?php t("Start time"); ?></th>
     <th><?php t("End time"); ?></th>
     <th><?php t("Foreman"); ?></th>
     <th><?php t("People"); ?></th>
     <th><?php t("Place"); ?></th>
     <th><?php t("Notes"); ?></th>
     <?php if($impostazioni['edit']) { echo "<th>".t("Edit", false)."</th>"; } ?>
     <?php if($impostazioni['delete']) { echo "<th>".t("Remove", false)."</th>"; } ?>
    </tr>
    </thead>
    <tbody>
<?php
foreach($risultato as $row){
      $foreman = $user->nameById($row["capo"]);
      $others_people = "";
      foreach(explode(",", $row['personale']) as $key=>$name){
        $others_people = $others_people.$user->nameById($name).", ";
      }
      echo "<tr><td>" . $row['data'] . "</td><td>" . $row['name'] . "</td><td>" . $row['inizio'] . "</td><td>" . $row['fine'] . "</td><td>" . $foreman . "</td><td>" . $others_people . "</td><td>" . $row['luogo'] . "</td><td>" . $row['note'] . "</td>";
      if($impostazioni['edit']) {
          echo "<td><a href='edit_training.php?edit&id={$row['id']}'><i style='font-size: 40px' class='fa fa-edit'></i></a></td>";
      }
      if($impostazioni['delete']) {
          echo "<td><a href='edit_training.php?delete&id={$row['id']}&incrementa={$row['incrementa']}'><i style='font-size: 40px' class='fa fa-trash'></i></a></td></tr>";
      }
}
?>
   </tbody>
  </table>
</div>
</div>
</div>
