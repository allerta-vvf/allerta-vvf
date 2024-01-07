import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { BsModalRef } from 'ngx-bootstrap/modal';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { AuthService } from 'src/app/_services/auth.service';

@Component({
  selector: 'modal-user-info',
  templateUrl: './modal-user-info.component.html',
  styleUrls: ['./modal-user-info.component.scss']
})
export class ModalUserInfoComponent implements OnInit {
  id = 0;
  loaded = false;

  canGoToEditPage = false;

  user: any = {};

  constructor(
    public bsModalRef: BsModalRef,
    private api: ApiClientService,
    public auth: AuthService,
    private router: Router
  ) { }

  ngOnInit() {
    this.api.get(`users/${this.id}`).then((response) => {
      this.user = response;
      this.loaded = true;
      console.log(response);
    }).catch((err) => {
      console.log(err);
    });
    this.canGoToEditPage = this.auth.profile.id === this.id || this.auth.profile.can('users-read');
  }

  goToEditPage() {
    if(!this.canGoToEditPage) return;
    this.bsModalRef.hide();
    this.router.navigate(['/users', this.id]);
  }
}
