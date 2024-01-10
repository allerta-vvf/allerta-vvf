import { Component, OnInit } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { ApiClientService } from 'src/app/_services/api-client.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-admin-info',
  templateUrl: './admin-info.component.html',
  styleUrls: ['./admin-info.component.scss']
})
export class AdminInfoComponent implements OnInit {
  allowedImageTypes = ["application/pdf"];
  maxImageSize = 1024 * 1024 * 50; //50MB

  LIFMNotUploadedYet = false;

  constructor(
    private translateService: TranslateService,
    private api: ApiClientService
  ) { }

  ngOnInit(): void {
  }

  onLIFMSelected(event: any) {
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
      this.LIFMNotUploadedYet = true;
    }
  }

  uploadLIFM(input: HTMLInputElement) {
    if(!input.files || !input.files[0]) return;

    console.log(input.files[0]);

    const formData = new FormData();
    formData.append('file', input.files[0], input.files[0].name);

    this.api.post("admin/lifm", formData).then((response: any) => {
      console.log(response);
      this.LIFMNotUploadedYet = false;
    }).catch((err: any) => {
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
