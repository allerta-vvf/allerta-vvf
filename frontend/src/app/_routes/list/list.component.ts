import { Component, OnInit, OnDestroy, ViewChild } from '@angular/core';
import { TableComponent } from '../../_components/table/table.component';
import { ModalAvailabilityScheduleComponent } from '../../_components/modal-availability-schedule/modal-availability-schedule.component';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { ToastrService } from 'ngx-toastr';
import { BsModalService, BsModalRef } from 'ngx-bootstrap/modal';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from 'src/app/_services/auth.service';

@Component({
  selector: 'app-list',
  templateUrl: './list.component.html',
  styleUrls: ['./list.component.scss']
})
export class ListComponent implements OnInit {
  scheduleModalRef?: BsModalRef;
  @ViewChild('table') table!: TableComponent;

  public loadAvailabilityInterval: NodeJS.Timer | undefined = undefined;

  public available: boolean | undefined = undefined;
  public manual_mode: boolean | undefined = undefined;

  constructor(
    private api: ApiClientService,
    private auth: AuthService,
    private toastr: ToastrService,
    private modalService: BsModalService,
    private translate: TranslateService
  ) {
    this.loadAvailability();
  }

  loadAvailability() {
    this.api.get("availability").then((response) => {
      this.available = response.available;
      this.manual_mode = response.manual_mode;
    });
  }

  changeAvailibility(available: 0|1, id?: number|undefined) {
    if(typeof id === 'undefined') {
      id = this.auth.profile.auth_user_id;
    }
    this.api.post("availability", {
      id: id,
      available: available
    }).then((response) => {
      let changed_user_msg = parseInt(response.updated_user) === parseInt(this.auth.profile.auth_user_id) ? "La tua disponibilità" : `La disponibilità di ${response.updated_user_name}`;
      let msg = available === 1 ? `${changed_user_msg} è stata impostata con successo.` : `${changed_user_msg} è stata rimossa con successo.`;
      this.toastr.success(msg);
      this.loadAvailability();
      this.table.loadTableData();
    });
  }

  updateManualMode(manual_mode: 0|1) {
    this.api.post("manual_mode", {
      manual_mode: manual_mode
    }).then((response) => {
      this.translate.get('list.manual_mode_updated_successfully').subscribe((res: string) => {
        this.toastr.success(res);
      });
      this.loadAvailability();
    });
  }

  openScheduleModal() {
    this.scheduleModalRef = this.modalService.show(ModalAvailabilityScheduleComponent, Object.assign({}, { class: 'modal-custom' }));
  }

  ngOnInit(): void {
    this.loadAvailabilityInterval = setInterval(() => {
      console.log("Refreshing availability...");
      this.loadAvailability();
    }, 10000);
    this.auth.authChanged.subscribe({
      next: () => this.loadAvailability()
    });
  }

  ngOnDestroy(): void {
    if(typeof this.loadAvailabilityInterval !== 'undefined') {
      clearInterval(this.loadAvailabilityInterval);
    }
  }

  requestTelegramToken() {
    this.api.post("telegram_login_token", {}).then((response) => {
      console.log(response);
      window.open(response.start_link, "_blank");
    });
  }

}
