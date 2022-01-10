import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { LatLng, latLng, tileLayer, Marker, Map } from 'leaflet';
import "leaflet.locatecontrol";

@Component({
  selector: 'map-picker',
  templateUrl: './map-picker.component.html',
  styleUrls: ['./map-picker.component.scss']
})
export class MapPickerComponent implements OnInit {
  @Input() lat = 45.88283872530;
  @Input() lng = 10.18226623535;

  @Output() onMarkerSet = new EventEmitter<any>();

  options = {
    layers: [
      tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' })
    ],
    zoom: 10,
    center: latLng(this.lat, this.lng)
  };
  isMarkerSet = false;
  marker: Marker;
  map: Map;
  lc: any;

  placeName = "";
  isPlaceSearchResultsOpen = false;
  placeSearchResults: any[] = [];

  constructor(private toastr: ToastrService, private api: ApiClientService) {
    this.marker = (window as any).L.marker(latLng(0,0));
    this.map = undefined as unknown as Map;
  }

  ngOnInit(): void { }

  setMarker(latLng: LatLng) {
    this.onMarkerSet.emit({
      lat: latLng.lat,
      lng: latLng.lng
    });
  
    const iconRetinaUrl = "./assets/icons/marker-icon-2x.png";
    const iconUrl = "./assets/icons/marker-icon.png";
    const shadowUrl = "./assets/icons/marker-shadow.png";
    const iconDefault = new (window as any).L.Icon({
      iconRetinaUrl,
      iconUrl,
      shadowUrl,
      iconSize: [25, 41],
      iconAnchor: [12, 41],
      popupAnchor: [1, -34],
      tooltipAnchor: [16, -28],
      shadowSize: [41, 41]
    });
    this.marker.remove();
    this.marker = (window as any).L.marker(latLng, { icon: iconDefault });
    this.isMarkerSet = true;
  }

  mapReady(map: any) {
    this.map = map;
    const this_class = this;
    (window as any).L.Control.CustomLocate = (window as any).L.Control.Locate.extend({
	    _drawMarker: function () {
        this_class.setMarker(this._event.latlng);
      },
      _onDrag: function () {},
      _onZoom: function () {},
      _onZoomEnd: function () {}
    });

    this.lc = new (window as any).L.Control.CustomLocate({
    	cacheLocation: false, // disabled for privacy reasons
      initialZoomLevel: 16
    }).addTo(map);
  }

  mapClick(e: any) {
    console.log(e);
    this.setMarker(e.latlng);
  }

  searchPlace() {
    if(this.placeName.length < 3) {
      this.toastr.error("Il nome della località deve essere di almeno 3 caratteri");
      return;
    }
    this.api.get("places/search", {
      q: this.placeName
    }).then((places) => {
      this.isPlaceSearchResultsOpen = true;
      this.placeSearchResults = places;
    }).catch((err) => {
      console.error(err);
      this.toastr.error("Errore di caricamento dei risultati della ricerca. Riprovare più tardi");
    });
  }

  selectPlace(place: any) {
    console.log(place);
    let latlng = latLng(place.lat, place.lon);
    this.setMarker(latlng);
    this.map.setView(latlng, 16);
  }

}
