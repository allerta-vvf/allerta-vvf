import jsZip from "jszip";
window.JSZip = jsZip;
import pdfMake from "pdfmake/build/pdfmake";
pdfMake.vfs = pdfFonts.pdfMake.vfs;
import pdfFonts from "pdfmake/build/vfs_fonts";
import "datatables.net-bs4/js/dataTables.bootstrap4.min.js";
import "datatables.net-bs4/css/dataTables.bootstrap4.min.css";
import "datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js";
import "datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css";
import "datatables.net-buttons";
import "datatables.net-buttons-bs4/js/buttons.bootstrap4.js";
import "datatables.net-buttons-bs4/css/buttons.bootstrap4.css";
import "datatables.net-buttons/js/buttons.html5.js";
import "datatables.net-buttons/js/buttons.print.js";

export default async function fillTable ({ data, replaceLatLngWithMap = false, callback = false }) {
  $("#table_body").empty();
  $.each(data, function (rowNum, item) {
    const row = document.createElement("tr");
    row.id = "row-" + rowNum;
    $.each(item, function (cellNum, i) {
      if (i !== null) {
        if (replaceLatLngWithMap && i.match(/[+-]?\d+([.]\d+)?[;][+-]?\d+([.]\d+)?/gm)) { /* credits to @visoom https://github.com/visoom */
          const lat = i.split(";")[0];
          const lng = i.split(";")[1];
          const mapDiv = document.createElement("div");
          mapDiv.className = "map";
          mapDiv.id = "map-" + rowNum;
          const mapScript = document.createElement("script");
          console.log("Load map", [lat, lng, mapDiv.id]);
          mapScript.appendChild(document.createTextNode("allertaJS.maps.loadMap(" + lat + ", " + lng + ", \"map-" + rowNum + "\", false)"));
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
  let loadedLanguage = {};
  try {
    let language = navigator.language || navigator.userLanguage;
    language = language.toLowerCase().replace("_", "-");
    language = "it_it";
    loadedLanguage = await import(`datatables.net-plugins/i18n/${language}.json`)
      .then(({ default: _ }) => {
        return _;
      });
  } catch (error) {
    console.error("Error loading DataTables translation:");
    console.log(error);
    loadedLanguage = {};
  }
  if (!$.fn.DataTable.isDataTable("#table")) {
    var tableDt = $("#table").DataTable({
      responsive: true,
      responsive: {
        details: {
            display: $.fn.dataTable.Responsive.display.childRowImmediate,
            type: 'none',
            target: ''
        }
      },
      language: loadedLanguage,
      buttons: ["excel", "pdf", "csv"]
    });
    tableDt.buttons().container()
      .appendTo("#table_wrapper :nth-child(1):eq(0)");

    if (callback !== false) {
      callback(tableDt);
    }
  } else {
    tableDt.rows().invalidate();
  }
  window.tableDt = tableDt;
}
