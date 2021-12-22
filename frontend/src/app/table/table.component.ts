import { Component, OnInit, Input } from '@angular/core';
import { TableType } from '../_models/TableType';
import { ApiClientService } from '../_services/api-client.service';

@Component({
  selector: 'app-table',
  templateUrl: './table.component.html',
  styleUrls: ['./table.component.scss']
})
export class TableComponent implements OnInit {

  @Input() sourceType?: string;

  public data: any = [];

  constructor(public apiClient: ApiClientService) {}

  ngOnInit(): void {
    console.log(this.sourceType);
    this.apiClient.get(this.sourceType || "list").then((data: any) => {
      console.log(data);
      this.data = data;
    });
  }

}
