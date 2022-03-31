import { Component, OnInit, OnDestroy } from '@angular/core';
import { BsModalRef } from 'ngx-bootstrap/modal';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { AuthService } from 'src/app/_services/auth.service';
import { ToastrService } from 'ngx-toastr';
import Swal from 'sweetalert2';

const isEqual = (...objects: any[]) => objects.every(obj => JSON.stringify(obj) === JSON.stringify(objects[0]));

@Component({
  selector: 'modal-alert',
  templateUrl: './modal-alert.component.html',
  styleUrls: ['./modal-alert.component.scss']
})
export class ModalAlertComponent implements OnInit, OnDestroy {
  id = 0;

  users: any[] = [];

  isAdvancedCollapsed = true;
  loadDataInterval: NodeJS.Timer | undefined = undefined;

  notes = "";

  alertEnabled = true;

  constructor(
    public bsModalRef: BsModalRef,
    private api: ApiClientService,
    public auth: AuthService,
    private toastr: ToastrService
  ) { }

  loadResponsesData() {
    this.api.get(`alerts/${this.id}`).then((response) => {
      if(this.alertEnabled !== response.enabled) this.alertEnabled = response.enabled;
      if(!isEqual(this.users, response.crew)) this.users = response.crew;
      if (this.notes === "" || this.notes === null) {
        if(!isEqual(this.notes, response.notes)) this.notes = response.notes;
      }
    });
  }

  ngOnInit() {
    this.loadDataInterval = setInterval(() => {
      if (typeof (window as any).skipTableReload !== 'undefined' && (window as any).skipTableReload) {
        return;
      }
      console.log("Refreshing responses data...");
      this.loadResponsesData();
    }, 2000);
    this.loadResponsesData();
  }

  ngOnDestroy() {
    if (this.loadDataInterval) {
      console.log("Clearing interval...");
      clearInterval(this.loadDataInterval);
    }
  }

  saveAlertSettings() {
    if(!this.auth.profile.hasRole('SUPER_EDITOR')) return;
    this.api.post(`alerts/${this.id}/settings`, {
      notes: this.notes
    }).then((response) => {
      this.toastr.success("Impostazioni salvate con successo");
    });
  }

  deleteAlert() {
    if(!this.auth.profile.hasRole('SUPER_EDITOR')) return;
    Swal.fire({
      title: "Sei sicuro di voler ritirare l'allarme?",
      text: "I vigili verranno avvisati dell'azione",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: "Si, rimuovi",
      cancelButtonText: "Annulla"
    }).then((result: any) => {
      if (result.isConfirmed) {
        this.api.delete(`alerts/${this.id}`).then((response) => {
          console.log(response);
          this.bsModalRef.hide();
          this.api.alertsChanged.next();
        /*
          this.translate.get('table.service_removed_successfully').subscribe((res: string) => {
            this.toastr.success(res);
          });
          this.loadTableData();
        }).catch((e) => {
          this.translate.get('table.service_removed_error').subscribe((res: string) => {
            this.toastr.error(res);
        */
        });
      }
    });
  }
}
