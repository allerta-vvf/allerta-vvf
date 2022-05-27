import { Component, OnInit, OnDestroy, ViewChild } from '@angular/core';
import { TableComponent } from '../../_components/table/table.component';
import { ModalAvailabilityScheduleComponent } from '../../_components/modal-availability-schedule/modal-availability-schedule.component';
import { ModalAlertComponent } from 'src/app/_components/modal-alert/modal-alert.component';
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
export class ListComponent implements OnInit, OnDestroy {
  scheduleModalRef?: BsModalRef;
  alertModalRef?: BsModalRef;
  @ViewChild('table') table!: TableComponent;

  public loadAvailabilityInterval: NodeJS.Timer | undefined = undefined;

  public available: boolean | undefined = undefined;
  public manual_mode: boolean | undefined = undefined;

  public alertLoading = false;

  constructor(
    public api: ApiClientService,
    public auth: AuthService,
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

  addAlertFull() {
    this.alertLoading = true;
    if(!this.auth.profile.hasRole('SUPER_EDITOR')) return;
    this.api.post("alerts", {
      type: "full"
    }).then((response) => {
      this.alertLoading = false;
      if(response?.status === "error") {
        this.toastr.error(response.message, undefined, {
          timeOut: 5000
        });
        return;
      }
      this.alertModalRef = this.modalService.show(ModalAlertComponent, {
        initialState: {
          id: response.id
        }
      });
      this.api.alertsChanged.next();
    });
  }

  addAlertSupport() {
    this.alertLoading = true;
    if(!this.auth.profile.hasRole('SUPER_EDITOR')) return;
    this.api.post("alerts", {
      type: "support"
    }).then((response) => {
      this.alertLoading = false;
      if(response?.status === "error") {
        this.toastr.error(response.message, undefined, {
          timeOut: 5000
        });
        return;
      }
      this.alertModalRef = this.modalService.show(ModalAlertComponent, {
        initialState: {
          id: response.id
        }
      });
      this.api.alertsChanged.next();
    });
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
      const a = document.createElement("a");
      a.setAttribute('href', response.start_link);
      a.setAttribute('target', '_blank');
      a.click();
    });
  }

}
