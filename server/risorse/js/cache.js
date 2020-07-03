$(document).ready(function(){ 
	$.get("risorse/ajax/ajax_cache.php", function(data, status){
       console.log(data);
       console.log(data.users);
       console.log(data.services);
       console.log(data.trainings);
       var db = new Dexie("offline");
       console.log(db.tables.length);
       if(db.tables.length !== 0){
        db.usersl.clear();
        db.services.clear();
        console.log("cleaned");
       }
       db.version(1).stores({
          users: '++id,name,available,caposquadra,autista,telefono,services,trainings,online,minuti_dispo,immagine',
          services: '++id,data,codice,uscita,rientro,capo,autisti,personale,luogo,note,tipo,incrementa,inseritoda'
       });
       $.each( data.users, function( key, val ) {
         db.users.put(val);
       });
       $.each( data.services, function( key, val ) {
         db.services.put(val);
       });
       window.db = db;
    }, "json");
});