import { Component, OnInit, OnDestroy, Input, Output, EventEmitter } from '@angular/core';
import { TableType } from 'src/app/_models/TableType';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { AuthService } from '../../_services/auth.service';

@Component({
  selector: 'app-table',
  templateUrl: './table.component.html',
  styleUrls: ['./table.component.scss']
})
export class TableComponent implements OnInit, OnDestroy {

  @Input() sourceType?: string;
  @Input() refreshInterval?: number;

  @Output() changeAvailability: EventEmitter<{user: number, newState: 0|1}> = new EventEmitter<{user: number, newState: 0|1}>();

  public data: any = [];

  public loadDataInterval: NodeJS.Timer | undefined = undefined;

  constructor(public apiClient: ApiClientService, public auth: AuthService) {}

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
    this.loadDataInterval = setInterval(() => {
      console.log("Refreshing data...");
      this.loadTableData();
    }, this.refreshInterval || 10000);
  }

  ngOnDestroy(): void {
    if(typeof this.loadDataInterval !== 'undefined') {
      clearInterval(this.loadDataInterval);
    }
  }

  onChangeAvailability(user: number, newState: 0|1) {
    if(this.auth.profile.full_viewer) {
      this.changeAvailability.emit({user, newState});
    }
  }
}
