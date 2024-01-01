import { Component, OnInit, ViewChild } from '@angular/core';
import { ToastrService } from 'ngx-toastr';
import { TranslateService } from '@ngx-translate/core';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { MapComponent } from 'src/app/_components/map/map.component';
import { LatLngTuple, Marker } from 'leaflet';

@Component({
  selector: 'app-stats',
  templateUrl: './stats-services.component.html',
  styleUrls: ['./stats-services.component.scss']
})
export class StatsServicesComponent implements OnInit {
  services: any[] = [];
  serviceNumber = 0;
  servicesFilterStart: Date | undefined;
  servicesFilterEnd: Date | undefined;

  chartServicesByUserData: any;
  chartServicesByChiefData: any;
  chartServicesByDriverData: any;
  chartServicesByTypeData: any;
  chartServicesByVillageData: any;
  chartServicesByMunicipalityData: any;
  
  @ViewChild("servicesMap") servicesMap!: MapComponent;

  users: any[] = [];
  types: any[] = [];

  range: (Date | undefined)[] | undefined = undefined;
  lastRange: (Date | undefined)[] | undefined = undefined;

  constructor(
    private toastr: ToastrService,
    private api: ApiClientService,
    private translate: TranslateService
  ) {
    this.api.get("list").then((users) => {
      this.users = users;
      console.log(this.users);
    }).catch((err) => {
      this.translate.get('edit_service.users_load_failed').subscribe((res: string) => {
        this.toastr.error(res);
      });
    });
    this.api.get("service_types").then((types) => {
      console.log(types);
      this.types = types;
    }).catch((err) => {
      this.translate.get('edit_service.types_load_failed').subscribe((res: string) => {
        this.toastr.error(res);
      });
    });
  }

  ngOnInit(): void {
    this.loadServices();
  }

  getUserNameById(id: number) {
    let user = this.users.find((user) => user.id === id);
    if (user) {
      return user.name;
    }
    return "";
  }
  getTypeNameById(id: number) {
    let type = this.types.find((type) => type.id === id);
    if (type) {
      return type.name;
    }
    return "";
  }

  extractStatsFromServices() {
    let people: Record<number, number> = {};
    let chiefs: Record<number, number> = {};
    let drivers: Record<number, number> = {};
    let types: Record<string, number> = {};
    let villages: Record<string, number> = {};
    let municipalities: Record<string, number> = {};

    for (let service of this.services) {
      let currPeople: Set<number> = new Set();
      currPeople.add(service.chief_id);
      chiefs[service.chief_id] = (chiefs[service.chief_id] || 0) + 1;

      for (let person of service.drivers) {
        currPeople.add(person.id);
        drivers[person.id] = (drivers[person.id] || 0) + 1;
      }
      for (let person of service.crew) {
        currPeople.add(person.id);
      }
      
      for (let person of currPeople) {
        if (!people[person]) {
          people[person] = 0;
        }
        people[person]++;
      }

      if (!types[service.type_id]) {
        types[service.type_id] = 0;
      }
      types[service.type_id]++;

      if (service.place.village === null) service.place.village = this.translate.instant("unknown");
      // Capitalize first letter
      service.place.village = service.place.village.charAt(0).toUpperCase() + service.place.village.slice(1);
      if (!villages[service.place.village]) {
        villages[service.place.village] = 0;
      }
      villages[service.place.village]++;

      if (service.place.municipality === null) service.place.municipality = this.translate.instant("unknown");
      // Capitalize first letter
      service.place.municipality = service.place.municipality.charAt(0).toUpperCase() + service.place.municipality.slice(1);
      if (!municipalities[service.place.municipality]) {
        municipalities[service.place.municipality] = 0;
      }
      municipalities[service.place.municipality]++;
    }
    console.log(people, chiefs, drivers, types);

    this.chartServicesByUserData = {
      labels: Object.keys(people).map((id) => this.getUserNameById(parseInt(id))),
      datasets: [
        {
          data: Object.values(people),
        }
      ]
    };
    this.chartServicesByChiefData = {
      labels: Object.keys(chiefs).map((id) => this.getUserNameById(parseInt(id))),
      datasets: [
        {
          data: Object.values(chiefs),
        }
      ]
    };
    this.chartServicesByDriverData = {
      labels: Object.keys(drivers).map((id) => this.getUserNameById(parseInt(id))),
      datasets: [
        {
          data: Object.values(drivers),
        }
      ]
    };

    this.chartServicesByTypeData = {
      labels: Object.keys(types).map((id) => this.getTypeNameById(parseInt(id))),
      datasets: [
        {
          data: Object.values(types),
        }
      ]
    };

    this.chartServicesByVillageData = {
      labels: Object.keys(villages),
      datasets: [
        {
          data: Object.values(villages),
        }
      ]
    };
    this.chartServicesByMunicipalityData = {
      labels: Object.keys(municipalities),
      datasets: [
        {
          data: Object.values(municipalities),
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

      this.serviceNumber = this.services.length;

      let serviceMapFitBoundsTuple = [];
      this.servicesMap.removeAllMarkers();
      for (let service of this.services) {
        const pos: LatLngTuple = [service.place.lat, service.place.lon];
        serviceMapFitBoundsTuple.push(pos);
        let marker = new Marker(pos);
        this.servicesMap.addMarker(marker);
      }
      this.servicesMap.setBounds(serviceMapFitBoundsTuple);

      this.extractStatsFromServices();
    }).catch((error: any) => {
      console.error(error);
      this.toastr.error(this.translate.instant("Error while loading services"));
    });
  }

  filterDateRangeChanged($event: Date[]) {
    console.log($event);
    if (typeof($event) !== "object" || ($event !== null && $event.length === 0)) {
      this.servicesFilterStart = undefined;
      this.servicesFilterEnd = undefined;
    } else {
      this.servicesFilterStart = $event[0];
      this.servicesFilterEnd = $event[1];
    }
    if(this.lastRange !== this.range) this.loadServices();
    this.lastRange = this.range;
  }

  serviceMapClick(e: any) {
    console.log(e);
  }
}
