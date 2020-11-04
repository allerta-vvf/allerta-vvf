jQuery = $;
window.$ = window.jQuery = $;
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import './font-awesome.scss';
import '../node_modules/bootstrap-cookie-alert/cookiealert.css';  // TODO: migrate to Bootstrap Italia
import 'bootstrap-datepicker';
import '../node_modules/bootstrap-toggle/css/bootstrap-toggle.css';
import '../node_modules/bootstrap-toggle/js/bootstrap-toggle.js';
import '../node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css';
import 'time-input-polyfill/auto';
import 'jquery-pjax';

$(document).pjax('a', '#content');
$(document).on('pjax:start', function() {
  if(window.loadTable_interval !== undefined){
    clearInterval(window.loadTable_interval);
    window.loadTable_interval = undefined;
  }
})

// Cookie functions from w3schools
function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
  var expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
function getCookie(cname) {
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(';');
  for (var i = 0; i < ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) === ' ') {
          c = c.substring(1);
      }
      if (c.indexOf(name) === 0) {
          return c.substring(name.length, c.length);
      }
  }
  return "";
}

$( document ).ajaxError(function(event, xhr, settings, error) {
    console.error("Error requesting content: "+error+" - status code "+xhr.status);
    console.log(event);
    console.log(xhr);
    console.log(settings);
});

$( document ).ready(function() {
    // From https://github.com/Wruczek/Bootstrap-Cookie-Alert/blob/gh-pages/cookiealert.js
    var cookieAlert = document.querySelector(".cookiealert");
    var acceptCookies = document.querySelector(".acceptcookies");
    if (!cookieAlert) {
       return;
    }
    cookieAlert.offsetHeight; // Force browser to trigger reflow (https://stackoverflow.com/a/39451131)
    // Show the alert if we cant find the "acceptCookies" cookie
    if (!getCookie("acceptCookies")) {
        cookieAlert.classList.add("show");
    }
    // When clicking on the agree button, create a 1 year
    // cookie to remember user's choice and close the banner
    acceptCookies.addEventListener("click", function () {
        setCookie("acceptCookies", true, 365);
        cookieAlert.classList.remove("show");

        // dispatch the accept event
        window.dispatchEvent(new Event("cookieAlertAccept"))
    });
});
if (getCookie("authenticated")) {
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('sw.js').then(registration => {
        console.log('SW registered: ', registration);
      }).catch(registrationError => {
        console.log('SW registration failed: ', registrationError);
      });
    });
  }
}

function fillTable(data){
  $("#table_body").empty();
  $.each(data, function(num, item) {
    var row = document.createElement("tr");
    $.each(item, function(num, i) {
      if(i !== null){
        var cell = document.createElement("td");
        cell.innerHTML = i;
        row.appendChild(cell);
      }
    });
    document.getElementById("table_body").appendChild(row);
  });
}

var offline = false;
var loadTable_interval = undefined;
function loadTable(table_page, set_interval=true, interval=10000){
    $.getJSON( "resources/ajax/ajax_"+table_page+".php", function( data, status, xhr ) {
      fillTable(data);
      var headers = new Headers();
      headers.append('date', Date.now());
      caches.open('tables-1').then((cache) => {
        cache.put('/table_'+table_page+'.json', new Response(xhr.responseText, {headers: headers}))
      });
      if(window.offline){ // if xhr request successful, client is online
        $("#offline_alert").hide(400);
        window.offline = false;
      }
    }).fail(function() {
      caches.open('tables-1').then(cache => {
        cache.match("/table_"+table_page+".json").then(response => {
          response.json().then(data => {
            fillTable(data);
            console.log("Table loaded from cache");
            $("#offline_update").text(new Date(parseInt(response.headers.get("date"))).toLocaleString());
          });
        });
      });
      if(!window.offline){ // if xhr request fails, client is offline
        $("#offline_alert").show(400);
        window.offline = true;
      }
    });
    if(set_interval){
      window.loadTable_interval = setInterval(function() {
        window.loadTable(table_page, false);
      }, interval);
    }
}
window.loadTable_interval = loadTable_interval;
window.fillTable = fillTable;
window.loadTable = loadTable;