import { Component, OnInit, EventEmitter } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { BsModalRef } from 'ngx-bootstrap/modal';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { AuthService } from 'src/app/_services/auth.service';
import { TranslateService } from '@ngx-translate/core';
import Swal from 'sweetalert2';

@Component({
  selector: 'modal-add-medical-examination',
  templateUrl: './modal-add-medical-examination.component.html',
  styleUrls: ['./modal-add-medical-examination.component.scss']
})
export class ModalAddMedicalExaminationComponent implements OnInit {
  userId: number = 0;

  form: FormGroup = this.formBuilder.group({
    date: [null, [Validators.required]],
    certifier: ['', [Validators.required]],
    expiration_date: [null, [Validators.required]],
    file: [null]
  });

  dateMaxDate = new Date();
  expirationDateMinDate = new Date(new Date().setDate(new Date().getDate() + 1)); //Tomorrow
  allowedImageTypes = ["application/pdf"];
  maxImageSize = 1024 * 1024 * 50; //50MB

  submitEvents: EventEmitter<any> = new EventEmitter();

  constructor(
    public bsModalRef: BsModalRef,
    private formBuilder: FormBuilder,
    private api: ApiClientService,
    public auth: AuthService,
    private translateService: TranslateService
  ) { }

  ngOnInit() { }

  onMedicalExaminationCertificateSelected(event: any) {
    const file: File = event.target.files[0];
  
    if (file) {
      if(!this.allowedImageTypes.includes(file.type)) {
        event.target.value = null;
        Swal.fire({
          title: this.translateService.instant("error_title"),
          text: this.translateService.instant("edit_user.image_format_not_supported"),
          icon: 'error',
          confirmButtonText: 'Ok'
        });
        return;
      }
      if(file.size > this.maxImageSize) {
        event.target.value = null;
        Swal.fire({
          title: this.translateService.instant("error_title"),
          text: this.translateService.instant("edit_user.file_too_big"),
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
    formValues.expiration_date = formValues.expiration_date ? new Date(formValues.expiration_date).toISOString() : null;

    console.log(formValues);

    const formData = new FormData();
    formData.append('user', this.userId.toString());
    formData.append('date', formValues.date);
    formData.append('certifier', formValues.certifier);
    formData.append('expiration_date', formValues.expiration_date);
    if(formValues.file) formData.append('file', formValues.file, formValues.file.name);

    this.api.post("documents/medical_examination", formData).then((response) => {
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
