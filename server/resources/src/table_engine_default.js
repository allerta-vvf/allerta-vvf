export default async function fillTable ({ data, replaceLatLngWithMap = false, callback = false }) {
  $("#table_body").empty();
  $.each(data, function (rowNum, item) {
    const row = document.createElement("tr");
    row.id = "row-" + rowNum;
    $.each(item, function (cell_num, i) {
      if (i !== null) {
        if (replaceLatLngWithMap && i.match(/[+-]?\d+([.]\d+)?[;][+-]?\d+([.]\d+)?/gm)) { /* credits to @visoom https://github.com/visoom */
          const lat = i.split(";")[0];
          const lng = i.split(";")[1];
          const mapDiv = document.createElement("div");
          mapDiv.className = "map";
          mapDiv.id = "map-" + rowNum;
          const mapScript = document.createElement("script");
          mapScript.appendChild(document.createTextNode("loadMap(" + lat + ", " + lng + ", \"map-" + rowNum + "\", false)"));
          mapDiv.appendChild(mapScript);
          const cell = document.createElement("td");
          cell.appendChild(mapDiv);
          row.appendChild(cell);
        } else {
          const cell = document.createElement("td");
          cell.innerHTML = i;
          row.appendChild(cell);
        }
      }
    });
    document.getElementById("table_body").appendChild(row);
  });
  if (callback !== false) {
    callback();
  }
}
