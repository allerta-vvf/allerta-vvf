import { Component, OnInit, OnDestroy, Input, Output, EventEmitter } from '@angular/core';
import { Router } from '@angular/router';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { AuthService } from '../../_services/auth.service';
import { ToastrService } from 'ngx-toastr';
import { TranslateService } from '@ngx-translate/core';
import Swal from 'sweetalert2';
import { PageChangedEvent } from 'ngx-bootstrap/pagination';

@Component({
  selector: 'app-table',
  templateUrl: './table.component.html',
  styleUrls: ['./table.component.scss']
})
export class TableComponent implements OnInit, OnDestroy {

  @Input() sourceType?: string;
  @Input() refreshInterval?: number;

  enablePaginationTypes: string[] = ['logs', 'services', 'trainings'];
  searchPropertiesBlacklist: string[] = [
    "chief_id",
    "type_id",
    "pivot",
    "place_id",
    "display_name",
    "licence",
    "lat",
    "lon",
    "id",
    "updated_at",
    "added_by_id",
    "updated_by_id",
    "changed_id",
    "editor_id"
  ]

  _maxPaginationSize: number = 10;
  _rowsPerPage: number = 20;
  
  @Input('maxPaginationSize')
  get maxPaginationSize(): any {
    return this._maxPaginationSize;
  }
  set maxPaginationSize(value: any) {
    if(!isNaN(value)) this._maxPaginationSize = value;
  }
  
  @Input('rowsPerPage')
  get rowsPerPage(): any {
    return this._rowsPerPage;
  }
  set rowsPerPage(value: any) {
    if(!isNaN(value)) this._rowsPerPage = value;
  }

  @Output() changeAvailability: EventEmitter<{user: number, newState: 0|1}> = new EventEmitter<{user: number, newState: 0|1}>();
  @Output() userImpersonate: EventEmitter<number> = new EventEmitter<number>();

  public data: any = [];
  public displayedData: any = [];
  public originalData: any = [];

  public loadDataInterval: NodeJS.Timer | undefined = undefined;

  public currentPage: number = 1;
  public totalElements: number = 1;

  public searchText: string = "";
  public searchData: any = [];

  constructor(
    private api: ApiClientService,
    public auth: AuthService,
    private router: Router,
    private toastr: ToastrService,
    private translate: TranslateService
  ) { }

  getTime() {
    return Math.floor(Date.now() / 1000);
  }

  getTS(date: string) {
    return Math.floor(new Date(date).getTime() / 1000);
  }

  loadTableData() {
    if(!this.sourceType) this.sourceType = "list";
    this.api.get(this.sourceType).then((data: any) => {
      this.data = data.filter((row: any) => typeof row.hidden !== 'undefined' ? !row.hidden : true);
      this.originalData = this.data;
      this.totalElements = this.data.length;
      if(this.currentPage == 1) this.displayedData = this.data.slice(0, this.rowsPerPage);
      if(this.sourceType === 'list') {
        this.api.availableUsers = this.data.filter((row: any) => row.available).length;
      }
      this.initializeSearchData();
    }).catch((e) => {
      console.error(e);
    });
  }

  pageChanged(event: PageChangedEvent): void {
    const startItem = (event.page - 1) * event.itemsPerPage;
    const endItem = event.page * event.itemsPerPage;
    this.displayedData = this.data.slice(startItem, endItem);
  }

  initializeSearchData() {
    const searchPropertiesBlacklist = this.searchPropertiesBlacklist;
    function flattenObj(obj: any, parent: any, res: any = {}){
      //Based on https://stackoverflow.com/a/56253298
      for(let key in obj){
        if(typeof obj[key] == 'undefined' || obj[key] == null) continue;
        if(searchPropertiesBlacklist.includes(key)) continue;
        let propName = parent ? parent + '_' + key : key;
        if(typeof obj[key] == 'object'){
          flattenObj(obj[key], propName, res);
        } else {
          res[propName] = obj[key];
        }
      }
      return res;
    }

    this.searchData = this.data.map((row: any) => flattenObj(row, null));
  }

  onSearchTextChange(search: string) {
    if(search.length == 0) {
      this.data = this.originalData;
      this.displayedData = this.data.slice(0, this.rowsPerPage);
      this.totalElements = this.data.length;
      return;
    }
    this.data = this.originalData.filter((row: any, index: number) => {
      return Object.values(this.searchData[index]).some((value: any) => {
        return value.toString().toLowerCase().includes(search.toLowerCase());
      });
    });
    this.displayedData = this.data.slice(0, this.rowsPerPage);
    this.totalElements = this.data.length;
  }

  ngOnInit(): void {
    console.log(this.sourceType);
    this.loadTableData();
    this.loadDataInterval = setInterval(() => {
      if(typeof (window as any).skipTableReload !== 'undefined' && (window as any).skipTableReload) {
        return;
      }
      console.log("Refreshing data...");
      this.loadTableData();
    }, this.refreshInterval || 10000);
    this.auth.authChanged.subscribe({
      next: () => this.loadTableData()
    });
  }

  ngOnDestroy(): void {
    if(typeof this.loadDataInterval !== 'undefined') {
      clearInterval(this.loadDataInterval);
    }
  }

  onChangeAvailability(user: number, newState: 0|1) {
    if(this.auth.profile.can('users-read')) {
      this.changeAvailability.emit({user, newState});
    }
  }

  onUserImpersonate(user: number) {
    if(this.auth.profile.can('users-impersonate')) {
      this.auth.impersonate(user).then(() => {
        this.loadTableData();
        this.userImpersonate.emit(1);
      });
    }
  }

  openPlaceDetails(id: number) {
    this.router.navigate(['/place-details', id]);
  }

  editService(id: number) {
    this.router.navigate(['/services', id]); 
  }

  deleteService(id: number) {
    this.translate.get(['table.yes_remove', 'table.cancel', 'table.remove_service_confirm', 'table.remove_service_text']).subscribe((res: { [key: string]: string; }) => {
      Swal.fire({
        title: res['table.remove_service_confirm'],
        text: res['table.remove_service_confirm_text'],
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: res['table.yes_remove'],
        cancelButtonText: res['table.cancel']
      }).then((result) => {
        if (result.isConfirmed) {
          this.api.delete(`services/${id}`).then((response) => {
            this.translate.get('table.service_deleted_successfully').subscribe((res: string) => {
              this.toastr.success(res);
            });
            this.loadTableData();
          }).catch((e) => {
            this.translate.get('table.service_deleted_error').subscribe((res: string) => {
              this.toastr.error(res);
            });
          });
        }
      });
    });
  }

  extractNamesFromObject(obj: any) {
    return obj.flatMap((e: any) => e.name);
  }
}
