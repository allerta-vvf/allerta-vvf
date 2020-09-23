<?php
include_once("../../core.php");
init_class();
$user->requirelogin();

$risultato = $database->exec("SELECT * FROM `%PREFIX%_log`  ORDER BY `date` DESC, `time` DESC", true);

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
  overflow-x: scroll;
   box-shadow: 2px 2px 25px rgba(0,0,0,0.5);
    border-radius: 15px;
  margin: auto;
 }
select {
  margin: 50px;
  width: 150px;
  padding: 5px 35px 5px 5px;
  font-size: 16px;
  border: 1px solid #ccc;
  height: 34px;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  /* background: url(http://www.stackoverflow.com/favicon.ico) 96% / 15% no-repeat #eee; */
}
/* CAUTION: IE hackery ahead */
select::-ms-expand {
    display: none; /* remove default arrow on ie10 and ie11 */
}
/* target Internet Explorer 9 to undo the custom arrow */
@media screen and (min-width:0\0) {
    select {
        background:none\9;
        padding: 5px\9;
    }
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
<div style="overflow-x:auto;">
<table style="width: 90%; text-align:center;">
    <thead>
    <tr>
     <th><?php t("Action"); ?></th>
     <th><?php t("Interested"); ?></th>
     <th><?php t("Made by"); ?></th>
     <th><?php t("Datetime"); ?></th>
    </tr>
    </thead>
    <tbody>
     <?php
     foreach($risultato as $row){
     if(!in_array($row['changed'], $hidden) OR in_array($user->name(), $hidden)){
      if(!is_null($row["changed"])){
        $changedName = $user->nameById($row["changed"]);
      } else {
        $changedName = "N/A";
      }
      if(!is_null($row["editor"])){
        $editorName = $user->nameById($row["editor"]);
      } else {
        $editorName = "N/A";
      }
      echo "<tr><td>" . $row["action"] . "</td><td>" . $changedName . "</td><td>" . $editorName ."</td><td>" . $row['date'] . " - ore " . $row['time'] . "</tr>";
      }
     }
     ?>
    </tbody>
</table>
</div>
