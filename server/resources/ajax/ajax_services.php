<?php
include_once("../../core.php");
init_class();
$user->requirelogin();

$impostazioni['edit'] = true;
$impostazioni['delete'] = true;

$risultato = $database->exec("SELECT * FROM `%PREFIX%_services` ORDER BY data DESC, uscita desc", true); // Pesco i dati della table e li ordino in base alla data
?>
<div style='margin: 20px 0;' class="mx-auto">
<div style='margin: 2px auto' id="new-search-area"></div>
<div class="table-responsive">
  <div style="overflow-x:auto;">
  <table id="services" cellspacing='0' class="display table table-striped table-bordered dt-responsive nowrap" style="width: 90%; text-align:center;">
    <thead>
    <tr>
     <th><?php t("Date"); ?></th>
     <th><?php t("Code"); ?></th>
     <th><?php t("Start time"); ?></th>
     <th><?php t("End time"); ?></th>
     <th><?php t("Chief"); ?></th>
     <th><?php t("Drivers"); ?></th>
     <th><?php t("People"); ?></th>
     <th><?php t("Place"); ?></th>
     <th><?php t("Notes"); ?></th>
     <th><?php t("Type"); ?></th>
     <?php if($impostazioni['edit']) { echo "<th>".t("Edit", false)."</th>"; } ?>
     <?php if($impostazioni['delete']) { echo "<th>".t("Remove", false)."</th>"; } ?>
    </tr>
    </thead>
    <tbody>
<?php
foreach($risultato as $row){
      $chief = $user->nameById($row["capo"]);
      $drivers = "";
      foreach(explode(",", $row['autisti']) as $key=>$name){
        $drivers = $drivers.$user->nameById($name).", ";
      }
      $others_people = "";
      foreach(explode(",", $row['personale']) as $key=>$name){
        $others_people = $others_people.$user->nameById($name).", ";
      }
      echo "<tr><td>" . $row['data'] . "</td><td>" . $row['codice'] . "</td><td>" . $row['uscita'] . "</td><td>" . $row['rientro'] . "</td><td>" . $chief . "</td><td>" . $drivers . "</td><td>" . $others_people . "</td><td>" . s($row['luogo'],false,true) . "</td><td>" . s($row['note'],false,true) . "</td><td>" . s($row['tipo'],false,true) . "</td>";
      if($impostazioni['edit']) {
          echo "<td><a href='edit_service.php?edit&id={$row['id']}'><i style='font-size: 40px' class='fa fa-edit'></i></a></td>";
      }
      if($impostazioni['delete']) {
          echo "<td><a href='edit_service.php?delete&id={$row['id']}&increment={$row['increment']}'><i style='font-size: 40px' class='fa fa-trash'></i></a></td></tr>";
      }
}
?>
    </tbody>
   </table>
 </div>
</div>
</div>