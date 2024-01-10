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
  constructor(
    private translateService: TranslateService,
    private api: ApiClientService
  ) { }

  ngOnInit(): void {
  }
}
