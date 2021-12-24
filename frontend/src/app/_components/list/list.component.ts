import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { ApiClientService } from 'src/app/_services/api-client.service';

@Component({
  selector: 'app-list',
  templateUrl: './list.component.html',
  styleUrls: ['./list.component.scss']
})
export class ListComponent implements OnInit {

  @ViewChild('table') table!: any;

  constructor(private api: ApiClientService) {}


  changeAvailibility(available: 0|1, id?: number|undefined) {
    this.api.post("availability", {
      id: id,
      available: available
    });
    this.table.loadTableData();
  }

  ngOnInit(): void {
  }

}
