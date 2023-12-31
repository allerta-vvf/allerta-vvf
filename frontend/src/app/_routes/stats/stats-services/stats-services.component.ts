import { Component, OnInit, ViewChild } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { TranslateService } from '@ngx-translate/core';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { MapComponent } from 'src/app/_components/map/map.component';
import { LatLngTuple, LatLngBounds, Marker } from 'leaflet';

@Component({
  selector: 'app-stats',
  templateUrl: './stats-services.component.html',
  styleUrls: ['./stats-services.component.scss']
})
export class StatsServicesComponent implements OnInit {
  services: any[] = [];
  servicesFilterStart: Date | undefined;
  servicesFilterEnd: Date | undefined;

  data: any;
  
  @ViewChild("servicesMap") servicesMap!: MapComponent;

  range: (Date | undefined)[] | undefined = undefined;

  constructor(
    private toastr: ToastrService,
    private api: ApiClientService,
    private translate: TranslateService
  ) { }

  ngOnInit(): void {
    this.loadServices();

    this.data = {
      labels: ['A', 'B', 'C'],
      datasets: [
        {
          data: [540, 325, 702]
        }
      ]
    };
  }

  loadServices() {
    this.api.get("stats/services", {
      from: this.servicesFilterStart ? this.servicesFilterStart.toISOString() : undefined,
      to: this.servicesFilterEnd ? this.servicesFilterEnd.toISOString() : undefined
    }).then((response: any) => {
      this.services = response;
      console.log(this.services);

      let serviceMapFitBoundsTuple = [];
      this.servicesMap.removeAllMarkers();
      for (let service of this.services) {
        const pos: LatLngTuple = [service.place.lat, service.place.lon];
        serviceMapFitBoundsTuple.push(pos);
        let marker = new Marker(pos);
        this.servicesMap.addMarker(marker);
      }
      this.servicesMap.setBounds(serviceMapFitBoundsTuple);
    }).catch((error: any) => {
      console.error(error);
      this.toastr.error(this.translate.instant("Error while loading services"));
    });
  }

  serviceMapClick(e: any) {
    console.log(e);
  }

  filterDateRangeChanged($event: Date[]) {
    console.log($event);
    if ($event === undefined) {
      this.servicesFilterStart = undefined;
      this.servicesFilterEnd = undefined;
    } else {
      this.servicesFilterStart = $event[0];
      this.servicesFilterEnd = $event[1];
    }
    this.loadServices();
  }
}
