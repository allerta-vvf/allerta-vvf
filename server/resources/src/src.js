window.$ = window.jQuery = $;
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import '../node_modules/@fortawesome/fontawesome-free/css/all.css';
import '../node_modules/@fortawesome/fontawesome-free/js/all.js'; // thanks to https://medium.com/@bshelling/use-fontawesome-with-webpack-24f629d7b962 and https://stackoverflow.com/questions/52376720/how-to-make-font-awesome-5-work-with-webpack
import '../node_modules/bootstrap-cookie-alert/cookiealert.css';  // TODO: migrate to Bootstrap Italia
import pickadate from 'pickadate'

import L from 'leaflet';
import 'leaflet.locatecontrol';
import '../node_modules/leaflet.locatecontrol/dist/L.Control.Locate.min.css'
import '../node_modules/leaflet/dist/leaflet.css';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'resources/dist/marker-icon-2x.png',
  iconUrl: 'resources/dist/marker-icon.png',
  shadowUrl: 'resources/dist/marker-shadow.png',
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
});