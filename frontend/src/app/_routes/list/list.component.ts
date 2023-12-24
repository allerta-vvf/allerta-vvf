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

  public loadAvailabilityInterval: any = undefined;

  public available: boolean | undefined = undefined;
  public manual_mode: boolean | undefined = undefined;

  public alertLoading = false;

  private etag = "";

  constructor(
    public api: ApiClientService,
    public auth: AuthService,
    private toastr: ToastrService,
    private modalService: BsModalService,
    private translate: TranslateService
  ) {
  }

  loadAvailability() {
    this.api.get("availability", {}, this.etag).then((response) => {
      if(this.api.isLastSame) return;
      this.etag = this.api.lastEtag;
      this.available = response.available;
      this.manual_mode = response.manual_mode;
    }).catch((err) => {
      if(err.status === 500) throw err;
    });
  }

  changeAvailibility(available: 0|1, id?: number|undefined) {
    if(typeof id === 'undefined') {
      id = this.auth.profile.id;
    }
    this.api.post("availability", {
      id: id,
      available: available
    }).then((response) => {
      let changed_user_msg = parseInt(response.updated_user_id) === parseInt(this.auth.profile.id) ? "La tua disponibilità" : `La disponibilità di ${response.updated_user_name}`;
      let msg = available === 1 ? `${changed_user_msg} è stata impostata con successo.` : `${changed_user_msg} è stata rimossa con successo.`;
      this.toastr.success(msg);
      this.loadAvailability();
      this.table.loadTableData();
    }).catch((err) => {
      if(err.status === 500) throw err;
      this.translate.get('list.availability_change_failed').subscribe((res: string) => {
        this.toastr.error(res);
      });
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
    }).catch((err) => {
      if(err.status === 500) throw err;
      this.translate.get('list.manual_mode_update_failed').subscribe((res: string) => {
        this.toastr.error(res);
      });
    });
  }

  openScheduleModal() {
    this.scheduleModalRef = this.modalService.show(ModalAvailabilityScheduleComponent, Object.assign({}, { class: 'modal-custom' }));
  }

  addAlert(type: string, ignoreWarning = false) {
    this.alertLoading = true;
    if(!this.auth.profile.can('users-read')) return;
    this.api.post("alerts", {
      type,
      ignoreWarning
    }).then((response) => {
      console.log(response);
      this.alertLoading = false;
      this.alertModalRef = this.modalService.show(ModalAlertComponent, {
        initialState: {
          id: response.alert.id
        }
      });
      this.api.alertsChanged.next();
    }).catch((err) => {
      console.log(err);
      this.alertLoading = false;
      if(err.error?.ignorable === true) {
        if(confirm(err.error.message)) {
          this.addAlert(type, true);
        }
        return;
      }
      if(err.error?.message === undefined) err.error.message = "Errore sconosciuto";
      this.toastr.error(err.error.message, undefined, {
        timeOut: 5000
      });
    });
  }

  ngOnInit(): void {
    this.loadAvailabilityInterval = setInterval(() => {
      this.loadAvailability();
    }, 30000);
    this.auth.authChanged.subscribe({
      next: () => this.loadAvailability()
    });
    this.loadAvailability();
  }

  ngOnDestroy(): void {
    if(typeof this.loadAvailabilityInterval !== 'undefined') {
      clearInterval(this.loadAvailabilityInterval as number);
    }
  }

  requestTelegramToken() {
    this.api.post("telegram_login_token", {}).then((response) => {
      console.log(response);
      const a = document.createElement("a");
      a.setAttribute('href', response.start_link);
      a.setAttribute('target', '_blank');
      a.click();
    }).catch((err) => {
      if(err.status === 500) throw err;
      this.translate.get('list.telegram_token_request_failed').subscribe((res: string) => {
        this.toastr.error(res);
      });
    });
  }

}
