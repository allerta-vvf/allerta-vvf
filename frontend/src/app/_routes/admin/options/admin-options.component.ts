import { Component, OnInit } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { AuthService } from 'src/app/_services/auth.service';
import { ToastrService } from 'ngx-toastr';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-admin-options',
  templateUrl: './admin-options.component.html',
  styleUrls: ['./admin-options.component.scss']
})
export class AdminOptionsComponent implements OnInit {
  options: any[] = [];

  constructor(
    private translateService: TranslateService,
    private api: ApiClientService,
    public auth: AuthService,
    private toastr: ToastrService
  ) { }

  getOptions() {
    this.api.get('admin/options').then((res: any) => {
      res.forEach((option: any) => {
        switch (option.type) {
          case 'boolean':
            option.value = option.value === '1' || option.value === 'true';
            break;
          case 'number':
            option.value = parseFloat(option.value);
            break;
          case 'string':
            option.value = option.value.toString();
            break;
        }
        //Add properties used in the UI
        option._origValue = option.value;
        option._updating = false;
      });
      this.options = res;
      console.log(this.options);
    }).catch((err: any) => {
      console.error(err);
      Swal.fire({
        title: this.translateService.instant("error_title"),
        text: err.error.message,
        icon: 'error',
        confirmButtonText: 'Ok'
      });
    });
  }

  ngOnInit() {
    this.getOptions();
  }

  updateOption(optionId: number) {
    let option = this.options.find(o => o.id === optionId);
    option._updating = true;
    console.log(option);
    this.api.put('admin/options/'+option.id, {
      value: option.value
    }).then((res: any) => {
      console.log(res);
      option._origValue = option.value;
      this.toastr.success(this.translateService.instant("admin.option_update_success"));
    }).catch((err: any) => {
      console.error(err);
      Swal.fire({
        title: this.translateService.instant("error_title"),
        text: err.error.message,
        icon: 'error',
        confirmButtonText: 'Ok'
      });
    }).finally(() => {
      option._updating = false;
    });
  }
}
