import { Component, OnInit, ViewChild, TemplateRef } from '@angular/core';
import { TableComponent } from '../table/table.component';
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

  openScheduleModal(template: TemplateRef<any>) {
    this.scheduleModalRef = this.modalService.show(template);
  }

  ngOnInit(): void {
  }

}
