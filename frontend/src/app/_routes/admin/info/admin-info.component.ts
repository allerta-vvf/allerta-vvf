import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { AuthService } from 'src/app/_services/auth.service';
import { ModalUserInfoComponent } from '../../../_components/modal-user-info/modal-user-info.component';
import { BsModalService } from 'ngx-bootstrap/modal';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-admin-info',
  templateUrl: './admin-info.component.html',
  styleUrls: ['./admin-info.component.scss']
})
export class AdminInfoComponent implements OnInit {
  info: any;

  constructor(
    private translateService: TranslateService,
    private api: ApiClientService,
    public auth: AuthService,
    private router: Router,
    private modalService: BsModalService
  ) { }

  getInfo() {
    this.api.get('admin/info').then((res: any) => {
      this.info = res;
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

  ngOnInit(): void {
    this.getInfo();
  }

  onUserImpersonate(user: number) {
    if(this.auth.profile.can('users-impersonate')) {
      this.router.navigate(['/list']);
      this.auth.impersonate(user).then(() => {
        console.log(user);
      });
    }
  }

  onMoreDetails(id: number) {
    if(this.auth.profile.can('users-update')) {
      this.modalService.show(ModalUserInfoComponent, {
        initialState: {
          id
        }
      });
    }
  }

  editUser(id: number) {
    this.router.navigate(['/users', id]);
  }
}
