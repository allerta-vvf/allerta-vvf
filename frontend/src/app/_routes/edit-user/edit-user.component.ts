import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { AuthService } from 'src/app/_services/auth.service';
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

  birthdayMaxDate = new Date(new Date().setFullYear(new Date().getFullYear() - 18)); //18 years ago
  dlExpirationMinDate = new Date(new Date().setDate(new Date().getDate() + 1)); //Tomorrow
  allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
  maxImageSize = 1024 * 1024 * 5; //5MB

  creation_date: string = "MAI";
  update_date: string = "MAI";
  last_access_date: string = "MAI";

  tmpDrivingLicenseImgData: string | null = null;
  dlScanNotUploadedYet = true;
  dlCurrScanUrl: string | null = null;

  constructor(
    private route: ActivatedRoute,
    private formBuilder: FormBuilder,
    private api: ApiClientService,
    private auth: AuthService
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
              scan: this.user.driving_license ? this.user.driving_license.uuid : null
            },
            suit_size: this.user.suit_size,
            boot_size: this.user.boot_size
          });

          const convertToItalianDate = (date: string | null): string => {
            if(!date) return "MAI";
            const dateObj = new Date(date);
            return dateObj.toLocaleString('it-IT', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
          }

          this.creation_date = convertToItalianDate(this.user.created_at);
          this.update_date = convertToItalianDate(this.user.updated_at);
          this.last_access_date = convertToItalianDate(this.user.last_access);

          if(this.user.driving_license && this.user.driving_license.document_file) {
            this.dlCurrScanUrl = this.api.apiEndpoint(this.user.driving_license.document_file.url);
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
    
    if(!canSetChief) this.profileForm.get('chief')?.disable();
    if(!canSetDriver) this.profileForm.get('driver')?.disable();
    if(!canBan) this.profileForm.get('banned')?.disable();
    if(!canHide) this.profileForm.get('hidden')?.disable();
  }

  onDrivingLicenseScanSelected(event: any) {
    const file: File = event.target.files[0];
    this.dlScanNotUploadedYet = true;
  
    if (file) {
      if(!this.allowedImageTypes.includes(file.type)) {
        event.target.value = null;
        Swal.fire({
          title: 'Errore',
          text: 'Formato immagine non supportato',
          icon: 'error',
          confirmButtonText: 'Ok'
        });
        return;
      }
      if(file.size > this.maxImageSize) {
        event.target.value = null;
        Swal.fire({
          title: 'Errore',
          text: 'File troppo grande',
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
    const filename = "test.png"

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
      //TODO: translate
      this.api.put(`users/${this.id}`, data).then((response) => {
        console.log(response);
        Swal.fire({
          title: 'Utente modificato',
          text: 'L\'utente è stato modificato con successo',
          icon: 'success',
          confirmButtonText: 'Ok'
        });
      }).catch((err) => {
        console.log(err);
        Swal.fire({
          title: 'Errore',
          text: 'Si è verificato un errore durante la modifica dell\'utente',
          icon: 'error',
          confirmButtonText: 'Ok'
        });
      });
    }
  }

}
