import { Component, OnInit, Output, EventEmitter } from '@angular/core';
import { LatLngTuple, LatLngBounds, latLng, tileLayer, Marker, Layer } from 'leaflet';

export declare class LeafletControlLayersConfig {
  baseLayers: {
    [name: string]: Layer;
  };
  overlays: {
    [name: string]: Layer;
  };
}
interface IRange {
  value: Date[];
  label: string;
}

@Component({
  selector: 'map',
  templateUrl: './map.component.html',
  styleUrls: ['./map.component.scss']
})
export class MapComponent implements OnInit {
  @Output() mapClick = new EventEmitter<any>();

  defaultLat = 45.88283872530;
  defaultLng = 10.18226623535;

  mapOptions = {
    zoom: 10,
    center: latLng(this.defaultLat, this.defaultLng)
  }
  mapLayersControl: LeafletControlLayersConfig = {
    baseLayers: {
      'Open Street Map': tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' }),
      'ESRI WorldImagery': tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community' }),
      'ESRI WorldTopoMap': tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', { attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (Hong Kong), and the GIS User Community' }),
      'Open Topo Map': tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)' }),
      'Open Street Map Hot': tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', { maxZoom: 20, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Tiles style by <a href="https://www.hotosm.org/" target="_blank">Humanitarian OpenStreetMap Team</a> hosted by <a href="https://openstreetmap.fr/" target="_blank">OpenStreetMap France</a>' }),
      'Stadia Dark': tileLayer('https://tiles.stadiamaps.com/tiles/alidade_smooth_dark/{z}/{x}/{y}{r}.png', { maxZoom: 20, attribution: '&copy; <a href="https://www.stadiamaps.com/" target="_blank">Stadia Maps</a> &copy; <a href="https://openmaptiles.org/" target="_blank">OpenMapTiles</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' }),
    },
    overlays: {
      'Open Railway Map': tileLayer('https://{s}.tiles.openrailwaymap.org/standard/{z}/{x}/{y}.png', { maxZoom: 19, attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | Map style: &copy; <a href="https://www.OpenRailwayMap.org">OpenRailwayMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)' }),
      'Open Fire Map': tileLayer('http://openfiremap.org/hytiles/{z}/{x}/{y}.png', { maxZoom: 19, attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | Map style: &copy; <a href="http://www.openfiremap.org">OpenFireMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)' }),
      'SafeCast': tileLayer('https://s3.amazonaws.com/te512.safecast.org/{z}/{x}/{y}.png', { maxZoom: 16, attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | Map style: &copy; <a href="https://blog.safecast.org/about/">SafeCast</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)' })
    }
  };
  mapLayers: Layer[] = [this.mapLayersControl.baseLayers['Open Street Map']];
  mapFitBounds: LatLngBounds = latLng(this.defaultLat, this.defaultLng).toBounds(3000);

  constructor() { }

  ngOnInit(): void { }

  _mapClick(event: any) {
    this.mapClick.emit(event);
  }

  setBounds(boundsTuple: LatLngTuple[] | undefined) {
    if(boundsTuple && boundsTuple.length > 0) {
      this.mapFitBounds = new LatLngBounds(boundsTuple);
    } else {
      this.mapFitBounds = latLng(this.defaultLat, this.defaultLng).toBounds(3000);
    }
  }

  addMarker(marker: Marker) {
    this.mapLayers.push(marker);
  }

  removeAllMarkers() {
    this.mapLayers.splice(1);
  }
}
