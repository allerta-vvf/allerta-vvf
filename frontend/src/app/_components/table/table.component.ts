import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { TableType } from 'src/app/_models/TableType';
import { ApiClientService } from 'src/app/_services/api-client.service';

@Component({
  selector: 'app-table',
  templateUrl: './table.component.html',
  styleUrls: ['./table.component.scss']
})
export class TableComponent implements OnInit {

  @Input() sourceType?: string;

  @Output() changeAvailability: EventEmitter<{user: number, newState: 0|1}> = new EventEmitter<{user: number, newState: 0|1}>();

  public data: any = [];

  constructor(public apiClient: ApiClientService) {}

  getTime() {
    return Math.floor(Date.now() / 1000);
  }

  loadTableData() {
    this.apiClient.get(this.sourceType || "list").then((data: any) => {
      console.log(data);
      this.data = data.filter((row: any) => {
        if(typeof row.hidden !== 'undefined') return !row.hidden;
        return true;
      });
    });
  }

  ngOnInit(): void {
    console.log(this.sourceType);
    this.loadTableData();
  }

}
