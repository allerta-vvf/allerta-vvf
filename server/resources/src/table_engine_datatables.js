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
          let lat = i.split(";")[0];
          let lng = i.split(";")[1];
          let mapImageID = undefined;
          if(lng.includes("#")){
            lng = lng.split("#")[0];
            mapImageID = i.split("#")[1];
          }
          const mapDiv = document.createElement("div");
          mapDiv.id = "map-" + rowNum;
          const mapModal = document.createElement("div");
          mapModal.id = "map-modal-" + rowNum;
          mapModal.classList.add("modal");
          mapModal.classList.add("map-modal");
          mapModal.setAttribute("role", "dialog");
          mapModal.setAttribute("tabindex", "-1");
          mapModal.innerHTML = `<div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body" id="map-modal-${rowNum}-body">
                <div id="map-container-${rowNum}" class="map"></div><br>
                <p>Lat: <b id="map-${rowNum}-lat">${lat}</b></p>
                <p>Lng: <b id="map-${rowNum}-lng">${lng}</b></p>
              </div>
            </div>
          </div>`;
          document.body.appendChild(mapModal);

          if(mapImageID !== undefined){
            const mapPreview = document.createElement("figure");

            const mapPreviewImage = document.createElement("img");
            console.log("Adding map image", [lat, lng, mapImageID, mapDiv.id]);
            mapPreviewImage.src = "resources/images/map_cache/" + mapImageID + ".png";
            mapPreview.appendChild(mapPreviewImage);

            const mapPreviewCaption = document.createElement("figcaption");

            const mapPreviewModalOpener = document.createElement("a");
            mapPreviewCaption.style.cursor = "pointer";
            mapPreviewModalOpener.id = "map-opener-" + rowNum;
            mapPreviewModalOpener.classList.add("map-opener");
            mapPreviewModalOpener.classList.add("pjax_disable");
            mapPreviewModalOpener.innerText = "Premi qui per aprire la mappa interattiva";
            mapPreviewCaption.appendChild(mapPreviewModalOpener);

            mapPreview.appendChild(mapPreviewCaption);

            mapDiv.appendChild(mapPreview);
          } else {
            const mapModalOpener = document.createElement("a");
            mapModalOpener.id = "map-opener-" + rowNum;
            mapModalOpener.href = "#";
            mapModalOpener.classList.add("map-opener");
            mapModalOpener.classList.add("pjax_disable");
            mapModalOpener.innerText = "Premi qui per aprire la mappa interattiva";
            mapDiv.appendChild(mapModalOpener);
          }
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
      buttons: ["excel", "pdf", "csv"],
      info: false //TODO: fix info string
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

$(function() {
  document.querySelector("tbody").addEventListener('click', function(e) {
    if(e.target.classList.contains("map-opener")) {
      console.log(e);
      let id = e.target.id.replace("map-opener-", "");
      console.log(id);
      $("#map-modal-"+id).modal('show');
    }
  });
  $('body').on('shown.bs.modal', function (e) {
    console.log(e);
    if(e.target.classList.contains("map-modal")) {
      let id = e.target.id.replace("map-modal-", "");
      console.log(id);
      let lat = $("#map-"+id+"-lat").text();
      let lng = $("#map-"+id+"-lng").text();
      console.log(lat);
      console.log(lng);
      allertaJS.maps.loadMap(lat, lng, "map-container-"+id, false, true);
    }
  });
});
