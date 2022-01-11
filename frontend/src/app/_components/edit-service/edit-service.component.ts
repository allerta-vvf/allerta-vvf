import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { FormBuilder, Validators } from '@angular/forms';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-edit-service',
  templateUrl: './edit-service.component.html',
  styleUrls: ['./edit-service.component.scss']
})
export class EditServiceComponent implements OnInit {
  addingService = false;
  serviceId: string | undefined;
  loadedService = {
    start: '',
    end: '',
    code: '',
    chief: '',
    drivers: [],
    crew: [],
    place: '',
    notes: '',
    type: ''
  };

  users: any[] = [];
  types: any[] = [];

  place_lat: number = 0;
  place_lng: number = 0;
  
  addingType = false;
  newType = "";

  serviceForm: any;
  private formSubmitAttempt: boolean = false;
  submittingForm = false;

  get start() { return this.serviceForm.get('start'); }
  get end() { return this.serviceForm.get('end'); }
  get code() { return this.serviceForm.get('code'); }
  get chief() { return this.serviceForm.get('chief'); }
  get drivers() { return this.serviceForm.get('drivers'); }
  get crew() { return this.serviceForm.get('crew'); }
  get place() { return this.serviceForm.get('place'); }
  get type() { return this.serviceForm.get('type'); }

  ngOnInit() {
    this.serviceForm = this.fb.group({
      start: [this.loadedService.start, [Validators.required]],
      end: [this.loadedService.end, [Validators.required]],
      code: [this.loadedService.code, [Validators.required, Validators.minLength(3)]],
      chief: [this.loadedService.chief, [Validators.required]],
      drivers: [this.loadedService.drivers, [Validators.required]],
      crew: [this.loadedService.crew, [Validators.required]],
      place: [this.loadedService.place, [Validators.required, Validators.minLength(3)]],
      notes: [this.loadedService.notes],
      type: [this.loadedService.type, [Validators.required, Validators.minLength(1)]]
    });
  }

  constructor(
    private route: ActivatedRoute,
    private api: ApiClientService,
    private toastr: ToastrService,
    private fb: FormBuilder
  ) {
    this.route.paramMap.subscribe(params => {
      this.serviceId = params.get('id') || undefined;
      if(this.serviceId === "new") {
        this.addingService = true;
      } else {
        this.api.get(`services/${this.serviceId}`).then((service) => {
          this.loadedService = service;

          let patch = Object.assign({}, service);
          patch.start = new Date(parseInt(patch.start));
          patch.end = new Date(parseInt(patch.end));
          patch.drivers = patch.drivers.split(";");
          patch.crew = patch.crew.split(";");
          this.serviceForm.patchValue(patch);
        });
      }
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

  onDriversCheckboxChange(event: any) {
    if (event.target.checked) {
      this.drivers.setValue([...this.drivers.value, event.target.value]);
    } else {
      this.drivers.setValue(this.drivers.value.filter((x: number) => x !== event.target.value));
    }
  }
  isDriverSelected(id: number) {
    return this.drivers.value.find((x: number) => x == id);
  }

  onCrewCheckboxChange(event: any) {
    if (event.target.checked) {
      this.crew.setValue([...this.crew.value, event.target.value]);
    } else {
      this.crew.setValue(this.crew.value.filter((x: number) => x !== event.target.value));
    }
  }
  isCrewSelected(id: number) {
    return this.crew.value.find((x: number) => x == id);
  }

  setPlace(lat: number, lng: number) {
    this.place_lat = lat;
    this.place_lng = lng;
    this.place.setValue(lat + ";" + lng);
    console.log("Place selected", lat, lng);
  }

  //https://loiane.com/2017/08/angular-reactive-forms-trigger-validation-on-submit/
  isFieldValid(field: string) {
    return this.formSubmitAttempt ? this.serviceForm.get(field).valid : true;
  }

  formSubmit() {
    this.formSubmitAttempt = true;
    if(this.serviceForm.valid) {
      this.submittingForm = true;
      let values = Object.assign({}, this.serviceForm.value);
      values.start = values.start.getTime();
      values.end = values.end.getTime();
      values.drivers = values.drivers.join(";");
      values.crew = values.crew.join(";");
      console.log(values);
      this.api.post("services", values).then((res) => {
        console.log(res);
        this.toastr.success("Intervento aggiunto con successo.");
        this.submittingForm = false;
      }).catch((err) => {
        console.error(err);
        this.toastr.error("Errore durante l'aggiunta dell'intervento");
        this.submittingForm = false;
      });
    }
  }

  formReset() {
    this.formSubmitAttempt = false;
    this.serviceForm.reset();
  }
}
