import { Component, OnInit, EventEmitter } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { BsModalRef } from 'ngx-bootstrap/modal';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { AuthService } from 'src/app/_services/auth.service';
import { TranslateService } from '@ngx-translate/core';
import Swal from 'sweetalert2';

@Component({
  selector: 'modal-add-training-course',
  templateUrl: './modal-add-training-course.component.html',
  styleUrls: ['./modal-add-training-course.component.scss']
})
export class ModalAddTrainingCourseComponent implements OnInit {
  userId: number = 0;

  form: FormGroup = this.formBuilder.group({
    type: [0, [Validators.required]],
    date: [null, [Validators.required]],
    doc_number: [null, [Validators.required]],
    file: [null]
  });

  dateMaxDate = new Date();
  expirationDateMinDate = new Date(new Date().setDate(new Date().getDate() + 1)); //Tomorrow
  allowedImageTypes = ["application/pdf"];
  maxImageSize = 1024 * 1024 * 50; //50MB

  addingType = false;
  newType = "";
  types: any[] = [];

  submitEvents: EventEmitter<any> = new EventEmitter();

  constructor(
    public bsModalRef: BsModalRef,
    private formBuilder: FormBuilder,
    private api: ApiClientService,
    public auth: AuthService,
    private translateService: TranslateService
  ) { }

  ngOnInit() {
    this.api.get("training_course_types").then((response) => {
      this.types = response;
    }).catch((err) => {
      console.log(err);
    });
  }

  addType() {
    this.addingType = false;
    this.api.post("training_course_types", { name: this.newType }).then((response) => {
      this.types.push(response);
      this.form.patchValue({
        type: response.id
      });
    }).catch((err) => {
      Swal.fire({
        title: this.translateService.instant("error_title"),
        text: err.error.message,
        icon: 'error',
        confirmButtonText: 'Ok'
      });
      console.log(err);
    });
  }

  onTrainingCourseDocumentSelected(event: any) {
    const file: File = event.target.files[0];
  
    if (file) {
      if(!this.allowedImageTypes.includes(file.type)) {
        event.target.value = null;
        Swal.fire({
          title: this.translateService.instant("error_title"),
          text: this.translateService.instant("validation.document_format_not_supported"),
          icon: 'error',
          confirmButtonText: 'Ok'
        });
        return;
      }
      if(file.size > this.maxImageSize) {
        event.target.value = null;
        Swal.fire({
          title: this.translateService.instant("error_title"),
          text: this.translateService.instant("validation.file_too_big"),
          icon: 'error',
          confirmButtonText: 'Ok'
        });
        return;
      }

      const reader = new FileReader();
      reader.readAsDataURL(file); 
      reader.onload = (_event) => {
        this.form.patchValue({
          file
        });
        this.form.get('file')?.updateValueAndValidity();
      }
    }
  }

  formSubmit() {
    const formValues = this.form.value;
    formValues.date = formValues.date ? new Date(formValues.date).toISOString() : null;

    console.log(formValues);

    const formData = new FormData();
    formData.append('user', this.userId.toString());
    formData.append('type', formValues.type);
    formData.append('date', formValues.date);
    formData.append('doc_number', formValues.doc_number);
    if(formValues.file) formData.append('file', formValues.file, formValues.file.name);

    this.api.post("documents/training_course", formData).then((response) => {
      console.log(response);
      this.bsModalRef.hide();
      this.submitEvents.emit();
    }).catch((err) => {
      Swal.fire({
        title: this.translateService.instant("error_title"),
        text: err.error.message,
        icon: 'error',
        confirmButtonText: 'Ok'
      });
      console.log(err);
    });
  }
}
