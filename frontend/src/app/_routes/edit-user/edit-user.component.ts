import { Component, OnInit, TemplateRef } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { AuthService } from 'src/app/_services/auth.service';
import { TranslateService } from '@ngx-translate/core';
import { ModalAddTrainingCourseComponent } from 'src/app/_components/modal-add-traning-course/modal-add-training-course.component';
import { ModalAddMedicalExaminationComponent } from 'src/app/_components/modal-add-medical-examination/modal-add-medical-examination.component';
import { BsModalService, BsModalRef } from 'ngx-bootstrap/modal';
import { ToastrService } from 'ngx-toastr';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-edit-user',
  templateUrl: './edit-user.component.html',
  styleUrls: ['./edit-user.component.scss']
})
export class EditUserComponent implements OnInit {
  id: number | undefined;
  user: any = {};

  profileForm: FormGroup = this.formBuilder.group({
    name: ['', [Validators.required, Validators.minLength(3)]],
    surname: [''],
    username: ['', [Validators.required, Validators.minLength(3)]],
    birthday: [null],
    birthplace: [''],
    birthplace_province: [''],
    ssn: [''],
    course_date: [null],
    driver: [false, [Validators.required]],
    chief: [false, [Validators.required]],
    banned: [false, [Validators.required]],
    hidden: [false, [Validators.required]],
    address: [''],
    address_zip_code: [''],
    phone_number: [''],
    email: [''],
    driving_license: this.formBuilder.group({
      number: [''],
      type: [''],
      expiration_date: [null],
      scan: [null]
    }),
    suit_size: [''],
    boot_size: ['']
  });

  hideTCAddBtn = true;
  hideMECertCol = true;
  hideMEAddBtn = true;
  birthdayMaxDate = new Date(new Date().setFullYear(new Date().getFullYear() - 18)); //18 years ago
  dlExpirationMinDate = new Date(new Date().setDate(new Date().getDate() + 1)); //Tomorrow
  allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
  maxImageSize = 1024 * 1024 * 5; //5MB

  creation_date: string = this.translateService.instant("never").toUpperCase();
  update_date: string = this.translateService.instant("never").toUpperCase();
  last_access_date: string = this.translateService.instant("never").toUpperCase();

  tmpDrivingLicenseImgData: string | null = null;
  dlScanNotUploadedYet = true;
  dlCurrScanUrl: string | null = null;

  resetPwdModalRef: BsModalRef | undefined;
  newPwd: string = '';
  newPwdConfirm: string = '';

  constructor(
    private route: ActivatedRoute,
    private formBuilder: FormBuilder,
    private api: ApiClientService,
    private auth: AuthService,
    private translateService: TranslateService,
    private modalService: BsModalService,
    private toastr: ToastrService
  ) {
    this.route.paramMap.subscribe(params => {
      this.id = typeof params.get('id') === 'string' ? parseInt(params.get('id') || '') : undefined;
      if (this.id) {
        this.api.get(`users/${this.id}`).then((response) => {
          this.user = response;
          console.log(response);

          this.profileForm.patchValue({
            name: this.user.name,
            surname: this.user.surname,
            username: this.user.username,
            birthday: this.user.birthday ? new Date(this.user.birthday) : null,
            birthplace: this.user.birthplace,
            birthplace_province: this.user.birthplace_province,
            ssn: this.user.ssn,
            course_date: this.user.course_date ? new Date(this.user.course_date) : null,
            driver: this.user.driver,
            chief: this.user.chief,
            banned: this.user.banned,
            hidden: this.user.hidden,
            address: this.user.address,
            address_zip_code: this.user.address_zip_code,
            phone_number: this.user.phone_number,
            email: this.user.email,
            driving_license: {
              number: this.user.driving_license ? this.user.driving_license.doc_number : null,
              type: this.user.driving_license ? this.user.driving_license.doc_type : null,
              expiration_date: (this.user.driving_license && this.user.driving_license.expiration_date) ? new Date(this.user.driving_license.expiration_date) : null,
              scan: this.user.driving_license ? this.user.driving_license.scan_uuid : null
            },
            suit_size: this.user.suit_size,
            boot_size: this.user.boot_size
          });

          const convertToItalianDate = (date: string | null): string => {
            if(!date) return this.translateService.instant("never").toUpperCase();
            const dateObj = new Date(date);
            return dateObj.toLocaleString('it-IT', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
          }

          this.creation_date = convertToItalianDate(this.user.created_at);
          this.update_date = convertToItalianDate(this.user.updated_at);
          this.last_access_date = convertToItalianDate(this.user.last_access);

          if(this.user.driving_license && this.user.driving_license.scan_uuid) {
            this.dlCurrScanUrl = this.api.apiEndpoint(this.user.driving_license.scan_url);
          }

          //If medical examination is present, check if at least one row has cert_url
          if(this.user.medical_examinations && this.user.medical_examinations.length > 0) {
            this.hideMECertCol = !this.user.medical_examinations.some((me: any) => {
              return me.cert_url;
            });
          }
        }).catch((err) => {
          console.log(err);
        });
      }
    });
  }

  ngOnInit(): void {
    let canSetChief = this.id == this.auth.profile.id ?
      this.auth.profile.can('user-set-chief') :
      this.auth.profile.can('users-set-chief');
    let canSetDriver = this.id == this.auth.profile.id ?
      this.auth.profile.can('user-set-driver') :
      this.auth.profile.can('users-set-driver');
    let canBan = this.id == this.auth.profile.id ?
      this.auth.profile.can('user-ban') :
      this.auth.profile.can('users-ban');
    let canHide = this.id == this.auth.profile.id ?
      this.auth.profile.can('user-hide') :
      this.auth.profile.can('users-hide');
    let canAddTrainingCourse = this.id == this.auth.profile.id ?
      this.auth.profile.can('user-add-training-course') :
      this.auth.profile.can('users-add-training-course');
    let canAddMedicalExamination = this.id == this.auth.profile.id ?
      this.auth.profile.can('user-add-medical-examination') :
      this.auth.profile.can('users-add-medical-examination');
    
    if(!canSetChief) this.profileForm.get('chief')?.disable();
    if(!canSetDriver) this.profileForm.get('driver')?.disable();
    if(!canBan) this.profileForm.get('banned')?.disable();
    if(!canHide) this.profileForm.get('hidden')?.disable();
    this.hideTCAddBtn = !canAddTrainingCourse;
    this.hideMEAddBtn = !canAddMedicalExamination;
  }

  onDrivingLicenseScanSelected(event: any) {
    const file: File = event.target.files[0];
    this.dlScanNotUploadedYet = true;
  
    if (file) {
      if(!this.allowedImageTypes.includes(file.type)) {
        event.target.value = null;
        Swal.fire({
          title: this.translateService.instant("error_title"),
          text: this.translateService.instant("validation.image_format_not_supported"),
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
        this.tmpDrivingLicenseImgData = reader.result as string; 
      }
    }
  }

  uploadDrivingLicenseScan(input: HTMLInputElement) {
    if(!input.files || !input.files[0]) return;

    console.log(input.files[0]);

    const formData = new FormData();
    formData.append('file', input.files[0], input.files[0].name);

    this.api.post("documents/driving_license", formData).then((response) => {
      console.log(response);
      this.dlScanNotUploadedYet = false;

      this.profileForm.patchValue({
        driving_license: {
          scan: response.uuid
        }
      });
    }).catch((err) => {
      console.log(err);
    });
  }

  formSubmit() {
    let data = this.profileForm.value;
    data.birthday = data.birthday ? new Date(data.birthday) : null;
    data.course_date = data.course_date ? new Date(data.course_date) : null;
    data.driving_license.expiration_date = data.driving_license.expiration_date ? new Date(data.driving_license.expiration_date) : null;

    if (this.id) {
      this.api.put(`users/${this.id}`, data).then((response) => {
        console.log(response);
        this.toastr.success(this.translateService.instant('edit_user.success_text'));
      }).catch((err) => {
        console.log(err);
        Swal.fire({
          title: this.translateService.instant("error_title"),
          text: err.error.message ? err.error.message : this.translateService.instant("edit_user.error_text"),
          icon: 'error',
          confirmButtonText: 'Ok'
        });
      });
    }
  }

  openModalAddTrainingCourse() {
    const modalReference = this.modalService.show(ModalAddTrainingCourseComponent, {
      initialState: {
        userId: this.id
      }
    });
    modalReference.content?.submitEvents.subscribe(() => {
      //Refresh user data after modal is closed
      this.api.get(`users/${this.id}`).then((response) => {
        this.user = response;
        console.log(response);
      }).catch((err) => {
        console.log(err);
      });
    });
  }

  openModalAddMedicalExamination() {
    const modalReference = this.modalService.show(ModalAddMedicalExaminationComponent, {
      initialState: {
        userId: this.id
      }
    });
    modalReference.content?.submitEvents.subscribe(() => {
      //Refresh user data after modal is closed
      this.api.get(`users/${this.id}`).then((response) => {
        this.user = response;
        console.log(response);

        //If medical examination is present, check if at least one row has cert_url
        if(this.user.medical_examinations && this.user.medical_examinations.length > 0) {
          this.hideMECertCol = !this.user.medical_examinations.some((me: any) => {
            return me.cert_url;
          });
        }
      }).catch((err) => {
        console.log(err);
      });
    });
  }

  openResetPwdModal(template: TemplateRef<any>) {
    this.resetPwdModalRef = this.modalService.show(template);
  }

  resetPwdSubmit() {
    //Check if min size 6
    if(this.newPwd.length < 6) {
      Swal.fire({
        title: this.translateService.instant("error_title"),
        text: this.translateService.instant("validation.password_min_length"),
        icon: 'error',
        confirmButtonText: 'Ok'
      });
      return;
    }
    //Check if pwd and confirm are equal
    if(this.newPwd !== this.newPwdConfirm) {
      Swal.fire({
        title: this.translateService.instant("error_title"),
        text: this.translateService.instant("password_not_match"),
        icon: 'error',
        confirmButtonText: 'Ok'
      });
      return;
    }
    this.api.put(`users/${this.id}/reset_password`, {
      password: this.newPwd
    }).then((response) => {
      console.log(response);
      this.toastr.success(this.translateService.instant('password_changed_successfully'));
      this.resetPwdModalRef?.hide();
    }).catch((err) => {
      console.log(err);
      Swal.fire({
        title: this.translateService.instant("error_title"),
        text: err.error.message,
        icon: 'error',
        confirmButtonText: 'Ok'
      });
    });
  }

}
