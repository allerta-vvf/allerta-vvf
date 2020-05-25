<?php
include_once("../../core.php");
init_class();
$user->requirelogin();

$risultato = $database->esegui("SELECT * FROM `%PREFIX%_log`", true);

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
     <th>Azione</th>
     <th>Interessato</th>
     <th>Fatto da</th>
     <th>Ora</th>
    </tr>
    </thead>
    <tbody>
     <?php
     foreach($risultato as $row){
     if(!in_array($row['subisce'], $hidden) OR in_array($user->name(), $hidden)){
      echo "<tr><td>" . $row["azione"] . "</td><td>" . $row["subisce"] . "</td><td>" . $row["agisce"] ."</td><td>" . $row['data'] . " - ore " . $row['ora'] . "</tr>";

      }
     }
     ?>
    </tbody>
</table>
</div>
