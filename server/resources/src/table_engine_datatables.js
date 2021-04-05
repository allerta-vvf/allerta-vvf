import jsZip from "jszip";
import pdfMake from "pdfmake/build/pdfmake";
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
window.JSZip = jsZip;
pdfMake.vfs = pdfFonts.pdfMake.vfs;

export default async function fillTable ({ data, replaceLatLngWithMap = false, callback = false }) {
  $("#table_body").empty();
  $.each(data, function (row_num, item) {
    const row = document.createElement("tr");
    row.id = "row-" + row_num;
    $.each(item, function (cell_num, i) {
      if (i !== null) {
        if (replaceLatLngWithMap && i.match(/[+-]?\d+([.]\d+)?[;][+-]?\d+([.]\d+)?/gm)) { /* credits to @visoom https://github.com/visoom */
          const lat = i.split(";")[0];
          const lng = i.split(";")[1];
          const mapDiv = document.createElement("div");
          mapDiv.className = "map";
          mapDiv.id = "map-" + row_num;
          const mapScript = document.createElement("script");
          mapScript.appendChild(document.createTextNode("load_map(" + lat + ", " + lng + ", \"map-" + row_num + "\", false)"));
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
    var table_dt = $("#table").DataTable({
      responsive: true,
      language: loadedLanguage,
      buttons: ["excel", "pdf", "csv"]
    });
    table_dt.buttons().container()
      .appendTo("#table_wrapper :nth-child(1):eq(0)");

    if (callback !== false) {
      callback(table_dt);
    }
  } else {
    table_dt.rows().invalidate();
  }
  window.table_dt = table_dt;
}
