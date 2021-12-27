import { Component, OnInit, ViewChild } from '@angular/core';
import { TableComponent } from '../table/table.component';
import { ModalAvailabilityScheduleComponent } from '../modal-availability-schedule/modal-availability-schedule.component';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { BsModalService, BsModalRef } from 'ngx-bootstrap/modal';

@Component({
  selector: 'app-list',
  templateUrl: './list.component.html',
  styleUrls: ['./list.component.scss']
})
export class ListComponent implements OnInit {
  scheduleModalRef?: BsModalRef;
  @ViewChild('table') table!: TableComponent;

  constructor(private api: ApiClientService, private modalService: BsModalService) {}

  changeAvailibility(available: 0|1, id?: number|undefined) {
    this.api.post("availability", {
      id: id,
      available: available
    }).then((response) => {
      this.table.loadTableData();
    });
  }

  openScheduleModal() {
    this.scheduleModalRef = this.modalService.show(ModalAvailabilityScheduleComponent, Object.assign({}, { class: 'modal-custom' }));
  }

  ngOnInit(): void {
  }

}
