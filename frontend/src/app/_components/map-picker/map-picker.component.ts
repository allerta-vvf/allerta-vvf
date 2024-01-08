import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { TranslateService } from '@ngx-translate/core';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { LatLng, latLng, tileLayer, Marker, Map, Icon } from 'leaflet';
import "leaflet.locatecontrol";

@Component({
  selector: 'map-picker',
  templateUrl: './map-picker.component.html',
  styleUrls: ['./map-picker.component.scss']
})
export class MapPickerComponent implements OnInit {
  lat = 45.88283872530;
  lng = 10.18226623535;

  @Input() selectLat = "";
  @Input() selectLng = "";

  @Output() markerSet = new EventEmitter<any>();

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

  constructor(private toastr: ToastrService, private api: ApiClientService, private translate: TranslateService) {
    this.marker = (window as any).L.marker(latLng(0,0));
    this.map = undefined as unknown as Map;
  }

  ngOnInit() {
    if(this.selectLat !== "" && this.selectLng !== "") {
      console.log(this.selectLat, this.selectLng);
      this.setMarker(latLng(parseFloat(this.selectLat), parseFloat(this.selectLng)));
    }
  }

  setMarker(latLng: LatLng) {
    this.markerSet.emit({
      lat: latLng.lat,
      lng: latLng.lng
    });
  
    const iconRetinaUrl = "./assets/icons/marker-icon-2x.png";
    const iconUrl = "./assets/icons/marker-icon.png";
    const shadowUrl = "./assets/icons/marker-shadow.png";
    const iconDefault = new Icon({
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
      this.translate.get('validation.place_min_length').subscribe((res: string) => {
        this.toastr.error(res);
      });
      return;
    }
    this.api.get("places/search", {
      q: this.placeName
    }).then((places) => {
      this.isPlaceSearchResultsOpen = true;
      this.placeSearchResults = places;
    }).catch((err) => {
      console.error(err);
      this.translate.get('map_picker.loading_error').subscribe((res: string) => {
        this.toastr.error(res);
      });
    });
  }

  selectPlace(place: any) {
    console.log(place);
    let latlng = latLng(place.lat, place.lon);
    this.setMarker(latlng);
    this.map.setView(latlng, 16);
  }

}
