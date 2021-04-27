export default async function fillTable ({ data, replaceLatLngWithMap = false, callback = false }) {
  $("#table_body").empty();
  $.each(data, function (rowNum, item) {
    const row = document.createElement("tr");
    row.id = "row-" + rowNum;
    $.each(item, function (cellNum, i) {
      if (i !== null) {
        const cell = document.createElement("td");
        cell.innerHTML = i;
        row.appendChild(cell);
      }
    });
    document.getElementById("table_body").appendChild(row);
  });
  if (callback !== false) {
    callback();
  }
}
