$(document).ready(function(){ 
	$.get("risorse/ajax/ajax_cache.php", function(data, status){
       console.log(data);
       console.log(data.vigili);
       console.log(data.interventi);
       console.log(data.esercitazioni);
       var db = new Dexie("offline");
       console.log(db.tables.length);
       if(db.tables.length !== 0){
        db.vigilil.clear();
        db.interventi.clear();
        console.log("cleaned");
       }
       db.version(1).stores({
          vigili: '++id,nome,disponibile,caposquadra,autista,telefono,interventi,esercitazioni,online,minuti_dispo,immagine',
          interventi: '++id,data,codice,uscita,rientro,capo,autisti,personale,luogo,note,tipo,incrementa,inseritoda'
       });
       $.each( data.vigili, function( key, val ) {
         db.vigili.put(val);
       });
       $.each( data.interventi, function( key, val ) {
         db.interventi.put(val);
       });
       window.db = db;
    }, "json");
});