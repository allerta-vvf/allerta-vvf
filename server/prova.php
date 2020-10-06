<?php
require_once 'core.php';
init_class(false);
?>
<html>
	<head>
		<title>Location picker example</title>

		<script src="resources/dist/main.js"></script>
	</head>
	<body>
		<style>
		.fa.fa-map-marker-alt,.fa.fa-spinner.fa-spin {
			line-height: inherit;
		}
		</style>
		<div id="map"></div>
		<div id="search">
			<input type="text" name="addr" value="" id="addr" size="10" />
			<button type="button" onclick="addr_search();" class="btn btn-primary">Search</button>
			<div id="results"></div>
		</div>
		<script>
var map;
var feature;
var marker;

function set_marker(LatLng){
	if(marker){
		console.log("Exists");
		console.log(marker);
		marker.remove();
	}
	console.log(LatLng);
	marker = L.marker(LatLng).addTo(map);
}

function load_map() {
	map = new L.Map('map', {zoomControl: true});

	var osmUrl = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
		osmAttribution = 'Map data &copy; 2012 <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
		osm = new L.TileLayer(osmUrl, {maxZoom: 18, attribution: osmAttribution});

	map.setView(new L.LatLng(45.5285, 10.2956), 10).addLayer(osm);

	var marker;
	map.on('click', function(e) {
	  set_marker(e.latlng);
	});

	L.Control.CustomLocate = L.Control.Locate.extend({
		_drawMarker: function() {
			set_marker(this._event.latlng);
	    }
    });

	var lc = new L.Control.CustomLocate({ flyTo: true, icon: "fa fa-map-marker-alt", drawCircle: false }).addTo(map);
}

function chooseAddr(lat1, lng1, lat2, lng2, osm_type, lat, lng) {
	var loc1 = new L.LatLng(lat1, lng1);
	var loc2 = new L.LatLng(lat2, lng2);
	var bounds = new L.LatLngBounds(loc1, loc2);
	console.log(lat1, lng1, lat2, lng2, osm_type);
	if (feature) {
		map.removeLayer(feature);
	}
	if (osm_type == "node") {
		map.fitBounds(bounds);
		map.setZoom(18);
	} else {
		var loc3 = new L.LatLng(lat1, lng2);
		var loc4 = new L.LatLng(lat2, lng1);
		feature = L.polyline( [loc1, loc4, loc2, loc3, loc1], {color: 'red'}).addTo(map);
		map.fitBounds(bounds);
	}
	set_marker(new L.LatLng(lat, lng));
}

function addr_search() {
    var inp = document.getElementById("addr");

    $.getJSON('https://nominatim.openstreetmap.org/search?format=json&limit=5&q=' + inp.value, function(data) {
        var items = [];

        $.each(data, function(key, val) {
            bb = val.boundingbox;
            items.push("<li><a href='#' onclick='chooseAddr(" + bb[0] + ", " + bb[2] + ", " + bb[1] + ", " + bb[3]  + ", \"" + val.osm_type + "\", " + val.lat + ", " + val.lon + ");return false;'>" + val.display_name + '</a></li>');
        });

		$('#results').empty();
        if (items.length != 0) {
            $('<p>', { html: "Search results:" }).appendTo('#results');
            $('<ul/>', {
                'class': 'my-new-list',
                html: items.join('')
            }).appendTo('#results');
        } else {
            $('<p>', { html: "No results found" }).appendTo('#results');
        }
    });
}

window.onload = load_map;
		</script>
		<style>
body {
	margin: 0;
}
div#map {
	width: 100%;
	height: 100%;
}
div#search {
	background-color: rgba(255, 255, 255, 0.4);
	z-index: 1000;
	position: absolute;
	bottom: 40px;
	left: 40px;
	width: auto;
	height: auto;
	padding: 10px;
}
div#search input {
	width: 200px;
}
div#results {
	font-style: sans-serif;
	color: black;
	font-size: 75%;
}
</style>
	</body>
</html>