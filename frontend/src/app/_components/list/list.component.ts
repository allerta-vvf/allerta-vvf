import { Component, OnInit, ViewChild } from '@angular/core';
import { TableComponent } from '../table/table.component';
import { ModalAvailabilityScheduleComponent } from '../modal-availability-schedule/modal-availability-schedule.component';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { ToastrService } from 'ngx-toastr';
import { BsModalService, BsModalRef } from 'ngx-bootstrap/modal';
import { AuthService } from 'src/app/_services/auth.service';

@Component({
  selector: 'app-list',
  templateUrl: './list.component.html',
  styleUrls: ['./list.component.scss']
})
export class ListComponent implements OnInit {
  scheduleModalRef?: BsModalRef;
  @ViewChild('table') table!: TableComponent;

  constructor(
    private api: ApiClientService,
    private auth: AuthService,
    private toastr: ToastrService,
    private modalService: BsModalService
  ) {}

  changeAvailibility(available: 0|1, id?: number|undefined) {
    this.api.post("availability", {
      id: id,
      available: available
    }).then((response) => {
      let changed_user_msg = parseInt(response.updated_user) === parseInt(this.auth.profile.auth_user_id) ? "La tua disponibilità" : `La disponibilità di ${response.updated_user_name}`;
      let msg = available === 1 ? `${changed_user_msg} è stata impostata con successo.` : `${changed_user_msg} è stata rimossa con successo.`;
      this.toastr.success(msg);
      this.table.loadTableData();
    });
  }

  openScheduleModal() {
    this.scheduleModalRef = this.modalService.show(ModalAvailabilityScheduleComponent, Object.assign({}, { class: 'modal-custom' }));
  }

  ngOnInit(): void {
  }

}