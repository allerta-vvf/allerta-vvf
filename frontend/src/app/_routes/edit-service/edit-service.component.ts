import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { AbstractControl, UntypedFormBuilder, ValidationErrors, Validators } from '@angular/forms';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { AuthService } from 'src/app/_services/auth.service';
import { ToastrService } from 'ngx-toastr';
import { TranslateService } from '@ngx-translate/core';

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
    lat: -1,
    lon: -1,
    provinceCode: '',
    municipalityCode: '',
    address: '',
    notes: '',
    type: ''
  };
  loadedServiceLat = "";
  loadedServiceLng = "";
  usingMapSelector = true;

  users: any[] = [];
  types: any[] = [];

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
  get lat() { return this.serviceForm.get('place.lat'); }
  get lon() { return this.serviceForm.get('place.lon'); }
  get address() { return this.serviceForm.get('place.address'); }
  get type() { return this.serviceForm.get('type'); }

  ngOnInit() {
    this.serviceForm = this.fb.group({
      start: [this.loadedService.start, [Validators.required]],
      end: [this.loadedService.end, [Validators.required]],
      code: [this.loadedService.code, [Validators.required, Validators.minLength(3)]],
      chief: [this.loadedService.chief, [Validators.required]],
      drivers: [this.loadedService.drivers, []],
      crew: [this.loadedService.crew, [Validators.required]],
      place: this.fb.group({
        lat: [this.loadedService.lat, this.usingMapSelector ?
          [Validators.required, (control: AbstractControl): ValidationErrors | null => {
            const valid = control.value >= -90 && control.value <= 90;
            return valid ? null : { 'invalidLatitude': { value: control.value } };
          }] : []
        ],
        lon: [this.loadedService.lon, this.usingMapSelector ?
          [Validators.required, (control: AbstractControl): ValidationErrors | null => {
            const valid = control.value >= -180 && control.value <= 180;
            return valid ? null : { 'invalidLongitude': { value: control.value } };
          }] : []
        ],
        provinceCode: [this.loadedService.provinceCode, this.usingMapSelector ?
          [] : [Validators.required, Validators.minLength(3)]],
        municipalityCode: [this.loadedService.municipalityCode, this.usingMapSelector ?
          [] : [Validators.required, Validators.minLength(3)]],
        address: [this.loadedService.address, this.usingMapSelector ?
          [] : [Validators.required, Validators.minLength(3)]]
      }),
      notes: [this.loadedService.notes],
      type: [this.loadedService.type, [Validators.required, Validators.minLength(1)]]
    });
  }

  constructor(
    private route: ActivatedRoute,
    private api: ApiClientService,
    public auth: AuthService,
    private toastr: ToastrService,
    private fb: UntypedFormBuilder,
    private translate: TranslateService,
    private router: Router
  ) {
    this.usingMapSelector = this.auth.profile.getOption("service_place_selection_use_map_picker", true);
    this.route.paramMap.subscribe(params => {
      this.serviceId = params.get('id') || undefined;
      if (this.serviceId === "new") {
        this.addingService = true;
      } else {
        this.api.get(`services/${this.serviceId}`).then((service) => {
          this.loadedService = service;
          this.loadedServiceLat = service.place.lat;
          this.loadedServiceLng = service.place.lon;

          this.chief.setValue(service.chief_id);

          console.log(service);

          let patch = Object.assign({}, service);
          patch.start = new Date(patch.start);
          patch.end = new Date(patch.end);
          patch.chief = patch.chief_id;
          patch.drivers = patch.drivers.map((e: any) => e.pivot.user_id+"");
          patch.crew = patch.crew.map((e: any) => e.pivot.user_id+"");
          patch.type = patch.type_id;
          this.serviceForm.patchValue(patch);
        }).catch((err) => {
          this.translate.get('edit_service.service_load_failed').subscribe((res: string) => {
            this.toastr.error(res);
          });
        });
      }
      console.log(this.serviceId);
    });
    this.api.get("list").then((users) => {
      this.users = users;
      console.log(this.users);
    }).catch((err) => {
      this.translate.get('edit_service.users_load_failed').subscribe((res: string) => {
        this.toastr.error(res);
      });
    });
    this.loadTypes();
  }

  loadTypes() {
    this.api.get("service_types").then((types) => {
      console.log(types);
      this.types = types;
    }).catch((err) => {
      this.translate.get('edit_service.types_load_failed').subscribe((res: string) => {
        this.toastr.error(res);
      });
    });
  }

  addType() {
    if (this.newType.length < 2) {
      this.translate.get('validation.type_must_be_two_characters_long').subscribe((res: string) => {
        this.toastr.error(res);
      });
      return;
    }
    if (this.types.find(t => t.name == this.newType)) {
      this.translate.get('validation.type_already_exists').subscribe((res: string) => {
        this.toastr.error(res);
      });
      return;
    }
    this.api.post("service_types", {
      name: this.newType
    }).then((type) => {
      this.addingType = false;
      this.newType = "";
      console.log(type);
      if (type.name) {
        this.translate.get('edit_service.type_added_successfully').subscribe((res: string) => {
          this.toastr.success(res);
        });
        this.loadTypes();
      }
    }).catch((err) => {
      this.translate.get('edit_service.type_add_failed').subscribe((res: string) => {
        this.toastr.error(res);
      });
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
    return this.drivers.value && this.drivers.value.find((x: number) => x == id);
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

  setPlaceMap(lat: number, lng: number) {
    this.lat.setValue(lat);
    this.lon.setValue(lng);
    console.log("Place selected", lat, lng);
  }

  setPlace(provinceCode: string, municipalityCode: string, address: string) {
    this.serviceForm.get('place.provinceCode').setValue(provinceCode);
    this.serviceForm.get('place.municipalityCode').setValue(municipalityCode);
    this.serviceForm.get('place.address').setValue(address);
    this.address.setValue(address);
    console.log("Place selected", provinceCode, municipalityCode, address);
  }

  //https://loiane.com/2017/08/angular-reactive-forms-trigger-validation-on-submit/
  isFieldValid(field: string) {
    if(!this.formSubmitAttempt) return true;
    if(this.serviceForm.get(field) == null) return false;
    return this.serviceForm.get(field).valid;
  }

  formSubmit() {
    console.log("form values", this.serviceForm.value);
    this.formSubmitAttempt = true;
    if (this.serviceForm.valid) {
      this.submittingForm = true;
      let values = Object.assign({}, this.serviceForm.value);
      values.start = values.start.getTime();
      values.end = values.end.getTime();
      console.log(values);
      if (this.serviceId !== "new") {
        values.id = this.serviceId;
        this.api.post("services", values).then((res) => {
          console.log(res);
          this.translate.get('edit_service.service_updated_successfully').subscribe((res: string) => {
            this.toastr.success(res);
          });
          this.submittingForm = false;
        }).catch((err) => {
          console.error(err);
          this.translate.get('edit_service.service_update_failed').subscribe((res: string) => {
            this.toastr.error(res);
          });
          this.submittingForm = false;
        });
      } else {
        this.api.post("services", values).then((res) => {
          console.log(res);
          this.translate.get('edit_service.service_added_successfully').subscribe((res: string) => {
            this.toastr.success(res);
            this.router.navigate(['/services']);
          });
          this.submittingForm = false;
        }).catch((err) => {
          if (err.error.message) {
            this.toastr.error(err.error.message);
          } else {
            this.translate.get('edit_service.service_add_failed').subscribe((res: string) => {
              this.toastr.error(res);
            });
          }
          this.submittingForm = false;
        });
      }
    }
  }

  formReset() {
    this.formSubmitAttempt = false;
    this.serviceForm.reset();
  }
}
