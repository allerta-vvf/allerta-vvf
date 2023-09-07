import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { ToastrService } from 'ngx-toastr';
import { TranslateService } from '@ngx-translate/core';
import { marker, latLng, tileLayer } from 'leaflet';

@Component({
  selector: 'place-details',
  templateUrl: './place-details.component.html',
  styleUrls: ['./place-details.component.scss']
})
export class PlaceDetailsComponent implements OnInit {
  id: number = 0;
  lat: number = 0;
  lon: number = 0;
  place_info: any = {};
  place_loaded = false;

  options = {};
  layers: any[] = [];

  constructor(
    private route: ActivatedRoute,
    private api: ApiClientService,
    private toastr: ToastrService,
    private translate: TranslateService
  ) {
    this.route.paramMap.subscribe(params => {
      this.id = parseInt(params.get('id') || '');

      this.api.get("places/"+this.id).then((place_info) => {
        this.place_info = place_info;
        console.log(this.place_info);

        this.lat = parseFloat(place_info.lat || '');
        this.lon = parseFloat(place_info.lon || '');
        console.log(this.lat, this.lon);

        this.options = {
          layers: [
            tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' })
          ],
          zoom: 17,
          center: latLng(this.lat, this.lon)
        };

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
        this.layers = [
          marker([this.lat, this.lon], {
            icon: iconDefault
          })
        ];

        this.place_loaded = true;
      }).catch((err) => {
        this.translate.get('place_details.place_load_failed').subscribe((res: any) => {
          this.toastr.error(res);
        });
      });
    });
  }

  ngOnInit() { }
}
