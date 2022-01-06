import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { ToastrService } from 'ngx-toastr';
import { latLng, tileLayer } from 'leaflet';
import "leaflet.locatecontrol";

@Component({
  selector: 'app-edit-service',
  templateUrl: './edit-service.component.html',
  styleUrls: ['./edit-service.component.scss']
})
export class EditServiceComponent implements OnInit {
  public serviceId: string | undefined;
  public users: any[] = [];
  public types: any[] = [];
  public addingType = false;
  public newType = "";
  public options = {
    layers: [
      tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '...' })
    ],
    zoom: 5,
    center: latLng(46.879966, -121.726909)
  };

  constructor(
    private route: ActivatedRoute,
    private api: ApiClientService,
    private toastr: ToastrService
  ) {
    this.route.paramMap.subscribe(params => {
      this.serviceId = params.get('id') || undefined;
      console.log(this.serviceId);
    });
    this.api.get("users").then((users) => {
      this.users = users;
      console.log(this.users);
    });
    this.loadTypes();
  }

  loadTypes() {
    this.api.get("service_types").then((types) => {
      console.log(types);
      this.types = types;
    });
  }

  ngOnInit(): void { }

  addType() {
    if(this.newType.length < 2) {
      this.toastr.error("Il nome della tipologia deve essere lungo almeno 2 caratteri");
      return;
    }
    if(this.types.find(t => t.name == this.newType)) {
      this.toastr.error("Il nome della tipologia è già in uso");
      return;
    }
    this.api.post("service_types", {
      name: this.newType
    }).then((type) => {
      this.addingType = false;
      this.newType = "";
      console.log(type);
      if(type === 1) this.toastr.success("Tipologia di servizio aggiunta con successo.");
      this.loadTypes();
    });
  }

  mapReady(map: any) {
    console.log(map);
    (window as any).L.control.locate().addTo(map);
  }

}
