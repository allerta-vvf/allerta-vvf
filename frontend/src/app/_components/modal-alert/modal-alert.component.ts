import { Component, OnInit, OnDestroy } from '@angular/core';
import { BsModalRef } from 'ngx-bootstrap/modal';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { AuthService } from 'src/app/_services/auth.service';
import { ToastrService } from 'ngx-toastr';
import { TranslateService } from '@ngx-translate/core';
import Swal from 'sweetalert2';

const isEqual = (...objects: any[]) => objects.every(obj => JSON.stringify(obj) === JSON.stringify(objects[0]));

@Component({
  selector: 'modal-alert',
  templateUrl: './modal-alert.component.html',
  styleUrls: ['./modal-alert.component.scss']
})
export class ModalAlertComponent implements OnInit, OnDestroy {
  id = 0;

  crewUsers: any[] = [];

  loadDataInterval: any = undefined;

  notes = "";
  originalNotes = "";
  notesHasUnsavedChanges = false;

  alertClosed = 0;

  private etag = "";

  constructor(
    public bsModalRef: BsModalRef,
    private api: ApiClientService,
    public auth: AuthService,
    private toastr: ToastrService,
    private translate: TranslateService
  ) { }

  loadResponsesData() {
    this.api.get(`alerts/${this.id}`, {}, this.etag).then((response) => {
      if(this.api.isLastSame) return;
      this.etag = this.api.lastEtag;
      this.alertClosed = response.closed;
      this.crewUsers = response.crew;
      if (!this.notesHasUnsavedChanges) {
        if(this.notes !== response.notes) {
          this.notes = response.notes;
          this.originalNotes = response.notes;
        }
      }
    }).catch((err) => {
      console.log(err);
    });
  }

  ngOnInit() {
    this.loadDataInterval = setInterval(() => {
      if (typeof (window as any).skipTableReload !== 'undefined' && (window as any).skipTableReload) {
        return;
      }
      console.log("Refreshing responses data...");
      this.loadResponsesData();
    }, 15000);
    this.loadResponsesData();
  }

  ngOnDestroy() {
    if (this.loadDataInterval) {
      console.log("Clearing interval...");
      clearInterval(this.loadDataInterval);
    }
  }

  notesUpdated() {
    this.notesHasUnsavedChanges = this.notes !== this.originalNotes;
  }

  saveAlertSettings() {
    if(!this.auth.profile.can('users-read')) return;
    this.api.patch(`alerts/${this.id}`, {
      notes: this.notes
    }).then((response) => {
      this.translate.get('alert.settings_updated_successfully').subscribe((res: string) => {
        this.toastr.success(res);
      });
      this.notesHasUnsavedChanges = false;
      this.originalNotes = this.notes;
    }).catch((err) => {
      console.log(err);
    });
  }

  deleteAlert() {
    if(!this.auth.profile.can('users-read')) return;
    this.translate.get([
      'alert.delete_confirm_title',
      'alert.delete_confirm_text',
      'table.yes_remove',
      'table.cancel',
      'alert.deleted_successfully',
      'alert.delete_failed'
    ]).subscribe((res: any) => {
      console.log(res);
      Swal.fire({
        title: res['alert.delete_confirm_title'],
        text: res['alert.delete_confirm_text'],
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: res['table.yes_remove'],
        cancelButtonText: res['table.cancel']
      }).then((result: any) => {
        if (result.isConfirmed) {
          this.api.patch(`alerts/${this.id}`, {
            closed: true
          }).then((response) => {
            console.log(response);
            this.bsModalRef.hide();
            this.api.alertsChanged.next();
            this.toastr.success(res['alert.deleted_successfully']);
            this.api.alertsChanged.next();
          }).catch((e) => {
            this.toastr.error(res['alert.delete_failed']);
          });
        }
      });
    });
  }

  getCurrentUserResponse() {
    const r = this.crewUsers.filter((c) => c.user.id === this.auth.profile.id);
    if(r.length === 0) return -1;
    return r[0].accepted;
  }

  setCurrentUserResponse(response: number) {
    if(!this.auth.profile.can('users-read')) return;
    this.api.post(`alerts/${this.id}/response`, {
      response
    }).then((response) => {
      this.translate.get('alert.response_updated_successfully').subscribe((res: string) => {
        this.toastr.success(res);
      });
      this.loadResponsesData();
    }).catch((e) => {
      this.toastr.error(e.error.message);
    });
  }
}
