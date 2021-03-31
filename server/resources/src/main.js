jQuery = $;
window.$ = window.jQuery = $;
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import './main.css';
import './font-awesome.scss';
import 'bootstrap-datepicker';
import '../node_modules/bootstrap-toggle/css/bootstrap-toggle.css';
import '../node_modules/bootstrap-toggle/js/bootstrap-toggle.js';
import '../node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css';
import 'time-input-polyfill/auto';
import 'jquery-pjax';
import toastr from 'toastr'
import 'toastr/build/toastr.css';
window.toastr = toastr;
toastr.options = {
  "closeButton": false,
  "debug": false,
  "newestOnTop": false,
  "progressBar": true,
  "positionClass": "toast-bottom-right",
  "preventDuplicates": false,
  "onclick": null,
  "showDuration": "300",
  "hideDuration": "1000",
  "timeOut": "5000",
  "extendedTimeOut": "1000",
  "showEasing": "swing",
  "hideEasing": "linear",
  "showMethod": "fadeIn",
  "hideMethod": "fadeOut"
}

$.fn.loading = function(action="start", options) {
  var opts = $.extend( {}, $.fn.loading.defaults, options );

  if(action == "show") {
      this.addClass("loading_blur");
      $( "body" ).append( "<div id='loading_div' class='loading_overlay'><p class=''><b>"+opts.message+"</b></p></div>" );
  } else if(action == "hide") {
      this.removeClass("loading_blur");
      this.addClass("loading_no_blur");
      setTimeout(() => {
        this.removeClass("loading_no_blur");
      }, 1000);
      $( "#loading_div" ).remove();
  }
};

$.fn.loading.defaults = {
  message: "Loading..."
};

console.log("Commit: "+process.env.GIT_VERSION);
console.log("Date: "+process.env.GIT_AUTHOR_DATE);
console.log("Bundle mode: "+process.env.BUNDLE_MODE);
console.log("Bundle date: "+new Date(process.env.BUNDLE_DATE).toISOString());

$(document).pjax('a:not(.pjax_disable)', '#content', {timeout: 100000});
$(document).on('pjax:start', function() {
  if(document.getElementById("topNavBar") !== undefined){
    document.getElementById("topNavBar").className = "topnav";
  }
  old_data = "null";
  fillTable = undefined;
  table_engine = "datatables";
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

if (getCookie("authenticated")) {
  var installServiceWorker = false;
  if(window.skipServiceWorkerInstallation !== undefined){ //if you want to disable SW for example via GreasyFork userscript
    installServiceWorker = false;
  }
  if(getCookie("disableServiceWorkerInstallation")){
    console.log("Skipping ServiceWorker installation because cookie 'disableServiceWorkerInstallation' exists");
    installServiceWorker = false;
  }
  if ('serviceWorker' in navigator) {
    if ('connection' in navigator && navigator.connection.saveData && !getCookie("forceServiceWorkerInstallation")) {
      console.log("Skipping ServiceWorker installation because saveData is enabled");
      installServiceWorker = false;
    }
    if ('storage' in navigator && 'estimate' in navigator.storage && !getCookie("forceServiceWorkerInstallation")){
      navigator.storage.estimate().then(quota => {
        const requiredMemory = 3 * 1e+6;
        if (quota < requiredMemory) {
          console.log("Skipping ServiceWorker installation because memory is low. memory="+quota);
          installServiceWorker = false;
        }
      });
    }
  } else {
    installServiceWorker = false;
  }
}
if(installServiceWorker){
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('sw.js').then(registration => {
      console.log('SW registered: ', registration);
    }).catch(registrationError => {
      console.log('SW registration failed: ', registrationError);
    });
  });
}

$( document ).ready(function() {
  if($("#frontend_version") !== undefined){
    $("#frontend_version").append(process.env.GIT_VERSION+" aggiornamento "+new Date(process.env.BUNDLE_DATE).toLocaleString());
  }
});

var offline = false;
var loadTable_interval = undefined;
var old_data = "null";
var table_engine = "datatables";
var fillTable = undefined;

async function loadTable({table_page, set_interval=true, interval=10000, onlineReload=false, use_custom_table_engine=false, callback=false}){
  if (typeof fillTable === "undefined"){
    if(use_custom_table_engine !== false){
      table_engine = use_custom_table_engine;
    } else if ('connection' in navigator && navigator.connection.saveData) {
      table_engine = "default";
    }
    fillTable = await import(`./table_engine_${table_engine}.js`)
    .then(({default: _}) => {
      return _;
    });
  }
  if ('getBattery' in navigator) {
    navigator.getBattery().then((level, charging) => {
      if (!charging && level < 0.2) {
        return;
      }
    })
  }
  if ('deviceMemory' in navigator && navigator.deviceMemory < 0.2) {
    return;
  }
  let replaceLatLngWithMap = table_page == "services" || table_page == "trainings";
  $.getJSON({ url: "resources/ajax/ajax_"+table_page+".php", data: { "old_data": old_data }, success: function( data, status, xhr ) {
    old_data = xhr.getResponseHeader('data'); //TODO: refactoring and adding comments
    console.log(data);
    if(data.length > 0){
      fillTable({data, replaceLatLngWithMap, callback});
      var headers = new Headers();
      headers.append('date', Date.now());
      caches.open('tables-1').then((cache) => {
        cache.put('/table_'+table_page+'.json', new Response(xhr.responseText, {headers: headers}))
      });
    }
    if(window.offline){ // if xhr request successful, client is online
      console.log(onlineReload);
      if(onlineReload){
        location.reload(); //for offline page
      } else {
        $("#offline_alert").hide(400);
        window.offline = false;
      }
    }
  }}).fail(function(data, status) {
    if(status == "parsererror"){
      if($("#table_body").children().length == 0) { //this is a server-side authentication error on some cheap hosting providers
        loadTable(table_page, set_interval, interval); //retry
      } // else nothing
    } else {
      caches.open('tables-1').then(cache => {
        cache.match("/table_"+table_page+".json").then(response => {
          response.json().then(data => {
            fillTable({data, replaceLatLngWithMap, callback});
            console.log("Table loaded from cache");
            $("#offline_update").text(new Date(parseInt(response.headers.get("date"))).toLocaleString());
          });
        });
      });
      if(!window.offline){ // if xhr request fails, client is offline
        $("#offline_alert").show(400);
        window.offline = true;
      }
    }
  });
  if(set_interval){
    if ('connection' in navigator && navigator.connection.saveData) {
      interval += 5000;
    }
    console.log("table_load interval "+interval);
    window.loadTable_interval = setInterval(function() {
      window.loadTable({table_page, set_interval: false, interval, onlineReload, use_custom_table_engine, callback: false});
    }, interval);
  }
}

function chat() {
  setCookie("chat", "true", 1);
  location.reload();
}

window.addEventListener('securitypolicyviolation',console.error.bind(console));

function menu() {
  var topNavBar = document.getElementById("topNavBar");
  if (topNavBar.className === "topnav") {
    topNavBar.className += " responsive";
  } else {
    topNavBar.className = "topnav";
  }
}

window.loadTable_interval = loadTable_interval;
window.loadTable = loadTable;
window.setCookie = setCookie;
window.getCookie = getCookie;
window.chat = chat;
window.menu = menu;