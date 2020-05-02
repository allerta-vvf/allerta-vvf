<?php
include_once("../../core.php");
init_class();
$utente->richiedilogin();

function ancora($content, $id) {
$content = substr($content,0,$limit);
$content = substr($content,0,strrpos($content,' '));
$content = $content." <a href='dettagli.php?iid=$id#note'>...Leggi ancora</a>";
return $content;
}

$impostazioni['modifica'] = false;
$impostazioni['elimina'] = false;

$risultato = $database->esegui("SELECT * FROM interventi ORDER BY data DESC, uscita desc", true); // Pesco i dati della tabella e li ordino in base alla data
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
  <table id="interventi" cellspacing='0' class="display table table-striped table-bordered dt-responsive nowrap" style="width: 90%; text-align:center;">
    <thead>
    <tr>
     <th>Data</th>
     <th>Codice</th>
     <th>Uscita</th>
     <th>Rientro</th>
     <th>Caposquadra</th>
     <th>Autisti</th>
     <th>Personale</th>
     <th>Luogo</th>
     <th>Note</th>
     <th>Tipo</th>
     <?php if($impostazioni['modifica']) { echo "<th>Modifica</th>"; } ?>
     <?php if($impostazioni['elimina']) { echo "<th>Elimina</th>"; } ?>
    </tr>
    </thead>
    <tbody>
<?php
foreach($risultato as $row){
      $persone = base64_encode( $row['incrementa'] );
      echo "<tr><td>" . $row['data'] . "</td><td>" . $row['codice'] . "</td><td>" . $row['uscita'] . "</td><td>" . $row['rientro'] . "</td><td>" . $row['capo'] . "</td><td>" . $row['autisti'] . "</td><td>" . $row['personale'] . "</td><td>" . $row['luogo'] . "</td><td>" . $row['note'] . "</td><td>" . $row['tipo'] . "</td>";
      if($impostazioni['modifica']) {
          echo "<td><a href='modifica.php?modifica&id={$row['id']}&data={$row['data']}&codice={$row['codice']}&uscita={$row['uscita']}&rientro={$row['rientro']}&luogo={$row['luogo']}&note={$row['note']}&tipo={$row['tipo']}'><i style='font-size: 40px' class='fa fa-edit'></i></a></td>";
      }
      if($impostazioni['elimina']) {
          echo "<td><a href='modifica.php?elimina&id={$row['id']}&persone={$persone}'><i style='font-size: 40px' class='fa fa-trash'></i></a></td></tr>";
      }
}
?>
    </tbody>
   </table>
 </div>
</div>
</div>