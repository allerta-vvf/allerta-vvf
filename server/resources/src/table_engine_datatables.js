import jsZip from 'jszip';
window.JSZip = jsZip;
import pdfMake from "pdfmake/build/pdfmake";
import pdfFonts from "pdfmake/build/vfs_fonts";
pdfMake.vfs = pdfFonts.pdfMake.vfs;
import 'datatables.net-bs4/js/dataTables.bootstrap4.min.js';
import 'datatables.net-bs4/css/dataTables.bootstrap4.min.css';
import 'datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js';
import 'datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css';
import 'datatables.net-buttons';
import 'datatables.net-buttons-bs4/js/buttons.bootstrap4.js';
import 'datatables.net-buttons-bs4/css/buttons.bootstrap4.css';
import 'datatables.net-buttons/js/buttons.html5.js';
import 'datatables.net-buttons/js/buttons.print.js';

export default async function fillTable(data, replaceLatLngWithMap=false){
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
  let loadedLanguage = {};
  try {
    let language = navigator.language || navigator.userLanguage;
    language = language.toLowerCase().replace("_","-");
    language = "it_it";
    loadedLanguage = await import(/* webpackChunkName: "DataTables_language_[request]" */ `datatables.net-plugins/i18n/${language}.json`)
    .then(({default: _}) => {
      return _;
    });
  } catch (error) {
    console.error("Error loading DataTables translation:");
    console.log(error);
    loadedLanguage = {};
  }
  let table = $('#table').DataTable({
    responsive: true,
    language: loadedLanguage,
    buttons: [ 'excel', 'pdf', 'csv' ]
  });
  table.buttons().container()
    .appendTo( '#table_wrapper :nth-child(1):eq(0)' );
}