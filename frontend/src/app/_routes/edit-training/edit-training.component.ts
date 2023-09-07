import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { UntypedFormBuilder, Validators } from '@angular/forms';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { ToastrService } from 'ngx-toastr';
import { TranslateService } from '@ngx-translate/core';

@Component({
  selector: 'app-edit-training',
  templateUrl: './edit-training.component.html',
  styleUrls: ['./edit-training.component.scss']
})
export class EditTrainingComponent implements OnInit {
  addingTraining = false;
  trainingId: string | undefined;
  loadedTraining = {
    start: '',
    end: '',
    name: '',
    chief: '',
    crew: [],
    place: '',
    notes: ''
  };

  users: any[] = [];

  trainingForm: any;
  private formSubmitAttempt: boolean = false;
  submittingForm = false;

  get start() { return this.trainingForm.get('start'); }
  get end() { return this.trainingForm.get('end'); }
  get name() { return this.trainingForm.get('name'); }
  get chief() { return this.trainingForm.get('chief'); }
  get crew() { return this.trainingForm.get('crew'); }
  get place() { return this.trainingForm.get('place'); }

  ngOnInit() {
    this.trainingForm = this.fb.group({
      start: [this.loadedTraining.start, [Validators.required]],
      end: [this.loadedTraining.end, [Validators.required]],
      name: [this.loadedTraining.name, [Validators.required, Validators.minLength(3)]],
      chief: [this.loadedTraining.chief, [Validators.required]],
      crew: [this.loadedTraining.crew, [Validators.required]],
      place: [this.loadedTraining.place, [Validators.required, Validators.minLength(3)]],
      notes: [this.loadedTraining.notes]
    });
  }

  constructor(
    private route: ActivatedRoute,
    private api: ApiClientService,
    private toastr: ToastrService,
    private fb: UntypedFormBuilder,
    private translate: TranslateService
  ) {
    this.route.paramMap.subscribe(params => {
      this.trainingId = params.get('id') || undefined;
      if (this.trainingId === "new") {
        this.addingTraining = true;
      } else {
        this.api.get(`trainings/${this.trainingId}`).then((training) => {
          this.loadedTraining = training;

          this.chief.setValue(training.chief_id);

          console.log(training);

          let patch = Object.assign({}, training);
          patch.start = new Date(patch.start);
          patch.end = new Date(patch.end);
          patch.chief = patch.chief_id;
          patch.crew = patch.crew.map((e: any) => e.pivot.user_id+"");
          this.trainingForm.patchValue(patch);
        }).catch((err) => {
          this.toastr.error("Errore nel caricare l'esercitazione. Ricarica la pagina e riprova.");
        });
      }
      console.log(this.trainingId);
    });
    this.api.get("list").then((users) => {
      this.users = users;
      console.log(this.users);
    }).catch((err) => {
      this.toastr.error("Errore nel caricare la lista degli utenti. Ricarica la pagina e riprova.");
    });
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

  //https://loiane.com/2017/08/angular-reactive-forms-trigger-validation-on-submit/
  isFieldValid(field: string) {
    return this.formSubmitAttempt ? this.trainingForm.get(field).valid : true;
  }

  formSubmit() {
    console.log("form values", this.trainingForm.value);
    this.formSubmitAttempt = true;
    if (this.trainingForm.valid) {
      this.submittingForm = true;
      let values = Object.assign({}, this.trainingForm.value);
      values.start = values.start.getTime();
      values.end = values.end.getTime();
      console.log(values);
      if (this.trainingId !== "new") {
        values.id = this.trainingId;
        this.api.post("trainings", values).then((res) => {
          console.log(res);
          this.translate.get('edit_training.training_added_successfully').subscribe((res: string) => {
            this.toastr.success(res);
          });
          this.submittingForm = false;
        }).catch((err) => {
          console.error(err);
          this.translate.get('edit_training.training_add_failed').subscribe((res: string) => {
            this.toastr.error(res);
          });
          this.submittingForm = false;
        });
      } else {
        this.api.post("trainings", values).then((res) => {
          console.log(res);
          this.translate.get('edit_training.training_added_successfully').subscribe((res: string) => {
            this.toastr.success(res);
          });
          this.submittingForm = false;
        }).catch((err) => {
          console.error(err);
          this.translate.get('edit_training.training_add_failed').subscribe((res: string) => {
            this.toastr.error(res);
          });
          this.submittingForm = false;
        });
      }
    }
  }

  formReset() {
    this.formSubmitAttempt = false;
    this.trainingForm.reset();
  }
}
