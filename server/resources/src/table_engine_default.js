export default function fillTable(data, replaceLatLngWithMap=false){
  $("#table_body").empty();
  $.each(data, function(row_num, item) {
    let row = document.createElement("tr");
    row.id = "row-"+row_num;
    $.each(item, function(cell_num, i) {
      if(i !== null){
        if(replaceLatLngWithMap && i.match(/[+-]?\d+([.]\d+)?[;][+-]?\d+([.]\d+)?/gm)){ /* credits to @visoom https://github.com/visoom */
          let lat = i.split(";")[0];
          let lng = i.split(";")[1];
          let mapDiv = document.createElement("div");
          mapDiv.className = "map";
          mapDiv.id = "map-"+row_num;
          let mapScript = document.createElement("script");
          mapScript.appendChild(document.createTextNode("load_map("+lat+", "+lng+", \"map-"+row_num+"\", false)"));
          mapDiv.appendChild(mapScript);
          let cell = document.createElement("td");
          cell.appendChild(mapDiv);
          row.appendChild(cell);
        } else {
          let cell = document.createElement("td");
          cell.innerHTML = i;
          row.appendChild(cell);
        }
      }
    });
    document.getElementById("table_body").appendChild(row);
  });
}