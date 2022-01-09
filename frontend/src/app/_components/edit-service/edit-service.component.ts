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
  serviceId: string | undefined;

  users: any[] = [];
  types: any[] = [];
  
  addingType = false;
  newType = "";

  serviceForm: any;
  private formSubmitAttempt: boolean = false;

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
      start: ['', [Validators.required]],
      end: ['', [Validators.required]],
      code: ['', [Validators.required, Validators.minLength(3)]],
      chief: ['', [Validators.required]],
      drivers: [[], [Validators.required]],
      crew: [[], [Validators.required]],
      place: ['', [Validators.required, Validators.minLength(3)]],
      notes: [''],
      type: ['', [Validators.required, Validators.minLength(1)]]
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

  onCrewCheckboxChange(event: any) {
    if (event.target.checked) {
      this.crew.setValue([...this.crew.value, event.target.value]);
    } else {
      this.crew.setValue(this.crew.value.filter((x: number) => x !== event.target.value));
    }
  }

  setPlace(lat: number, lng: number) {
    console.log("Place selected", lat, lng);
    this.place.setValue(lat + ";" + lng);
  }

  //https://loiane.com/2017/08/angular-reactive-forms-trigger-validation-on-submit/
  isFieldValid(field: string) {
    return this.formSubmitAttempt ? this.serviceForm.get(field).valid : true;
  }

  formSubmit() {
    this.formSubmitAttempt = true;
    if(this.serviceForm.valid) {
      let origValues = this.serviceForm.value; //very simple hack to get the original values

      let values = this.serviceForm.value;
      values.start = values.start.getTime();
      values.end = values.end.getTime();
      values.drivers = values.drivers.join(";");
      values.crew = values.crew.join(";");
      console.log(values);
      this.api.post("services", values).then((res) => {
        console.log(res);
        this.toastr.success("Intervento aggiunto con successo.");
      }).catch((err) => {
        console.error(err);
        this.toastr.error("Errore durante l'aggiunta dell'intervento");
      });

      this.serviceForm.value = origValues;
    }
  }

  formReset() {
    this.formSubmitAttempt = false;
    this.serviceForm.reset();
  }
}
