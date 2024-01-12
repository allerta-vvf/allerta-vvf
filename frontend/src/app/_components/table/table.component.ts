import { Component, OnInit, OnDestroy, Input, Output, EventEmitter, TemplateRef } from '@angular/core';
import { Router } from '@angular/router';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { AuthService } from '../../_services/auth.service';
import { ToastrService } from 'ngx-toastr';
import { PageChangedEvent } from 'ngx-bootstrap/pagination';
import { TranslateService } from '@ngx-translate/core';
import Swal from 'sweetalert2';
import * as UAParser from 'ua-parser-js';
import { BsModalRef, BsModalService } from 'ngx-bootstrap/modal';

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
  ];

  enableDateRangePickerTypes: string[] = ['services', 'trainings', 'logs'];
  range: (Date | undefined)[] | undefined = undefined;
  lastRange: (Date | undefined)[] | undefined = undefined;
  rangePicked = false;
  filterStart: Date | undefined;
  filterEnd: Date | undefined;

  @Input() initialStartFilter: Date | undefined;

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
  @Output() moreDetails: EventEmitter<{rowId: number}> = new EventEmitter<{rowId: number}>();

  public data: any = [];
  public displayedData: any = [];
  public originalData: any = [];
  private etag: string = "";

  public loadDataInterval: any = undefined;

  public currentPage: number = 1;
  public totalElements: number = 1;

  public searchText: string = "";
  public searchData: any = [];

  public useragentInfoModalRef: BsModalRef | undefined;
  public processedUA: any = {};

  constructor(
    private api: ApiClientService,
    public auth: AuthService,
    private router: Router,
    private toastr: ToastrService,
    private translate: TranslateService,
    private modalService: BsModalService
  ) { }

  loadTableData() {
    if(!this.sourceType) this.sourceType = "list";
    this.api.get(this.sourceType, this.rangePicked ? {
      from: this.filterStart ? this.filterStart.toISOString() : undefined,
      to: this.filterEnd ? this.filterEnd.toISOString() : undefined
    } : {}, this.etag).then((data: any) => {
      if(this.api.isLastSame) return;
      this.etag = this.api.lastEtag;
      if(typeof data === 'undefined' || data === null) return;
      this.data = data.filter((row: any) => typeof row.hidden !== 'undefined' ? !row.hidden : true);
      this.originalData = this.data;
      this.totalElements = this.data.length;
      this.displayedData = this.data.slice((this.currentPage - 1) * this.rowsPerPage, this.currentPage * this.rowsPerPage);
      if(this.sourceType === 'list') {
        this.api.availableUsers = this.data.filter((row: any) => row.available).length;
      }
      this.initializeSearchData();
    }).catch((e) => {
      console.error(e);
    });
  }

  filterDateRangeChanged($event: Date[]) {
    console.log($event);
    if (typeof($event) !== "object" || ($event !== null && $event.length === 0)) {
      this.filterStart = undefined;
      this.filterEnd = undefined;
      this.rangePicked = false;
    } else {
      this.filterStart = $event[0];
      this.filterEnd = $event[1];
      this.rangePicked = true;
    }
    if(this.lastRange !== this.range) this.loadTableData();
    this.lastRange = this.range;
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
    if(this.initialStartFilter !== undefined) {
      this.filterStart = this.initialStartFilter;
      this.filterEnd = new Date();
      this.rangePicked = true;
      this.range = [this.filterStart, this.filterEnd];
    }
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
      clearInterval(this.loadDataInterval as number);
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
      }).catch((errMessage: any) => {
        console.error(errMessage);
        Swal.fire({
          title: this.translate.instant("error_title"),
          text: errMessage,
          icon: 'error',
          confirmButtonText: 'Ok'
        });
      });
    }
  }

  onMoreDetails(rowId: number) {
    if(this.auth.profile.can('users-update')) {
      this.moreDetails.emit({rowId});
    }
  }

  editUser(id: number) {
    this.router.navigate(['/users', id]);
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

  editTraining(id: number) {
    this.router.navigate(['/trainings', id]); 
  }

  deleteTraining(id: number) {
    this.translate.get(['table.yes_remove', 'table.cancel', 'table.remove_training_confirm', 'table.remove_training_text']).subscribe((res: { [key: string]: string; }) => {
      Swal.fire({
        title: res['table.remove_training_confirm'],
        text: res['table.remove_training_confirm_text'],
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: res['table.yes_remove'],
        cancelButtonText: res['table.cancel']
      }).then((result) => {
        if (result.isConfirmed) {
          this.api.delete(`trainings/${id}`).then((response) => {
            this.translate.get('table.training_deleted_successfully').subscribe((res: string) => {
              this.toastr.success(res);
            });
            this.loadTableData();
          }).catch((e) => {
            this.translate.get('table.training_deleted_error').subscribe((res: string) => {
              this.toastr.error(res);
            });
          });
        }
      });
    });
  }

  extractNamesFromObject(obj: any) {
    if(typeof obj === 'undefined') return "";
    return obj.flatMap((e: any) => {
      if(e.surname === null) return e.name;
      return e.surname+" "+e.name;
    });
  }

  userAgentToIcons(userAgentString: string) {
    const parser = new UAParser(userAgentString);
    let icons = [];

    switch (parser.getBrowser().name) {
      case 'Chrome':
      case 'Chromium':
      case 'Chrome WebView':
      case 'Chrome Headless':
        icons.push('fab fa-chrome');
        break;
      case 'Mozilla':
      case 'Firefox [Focus/Reality]':
        icons.push('fab fa-firefox-browser');
        break;
      case 'Safari':
        icons.push('fab fa-safari');
        break;
      case 'IE':
      case 'IEMobile':
        icons.push('fa fa-skull-crossbones');
        break;
      case 'Edge':
        icons.push('fab fa-edge');
        break;
      case 'Android Browser':
      case 'Huawei Browser':
        case 'Samsung Browser':
        icons.push('fab fa-android');
        break;
      case 'Silk':
        icons.push('fab fa-amazon');
        break;
      case 'Instagram':
      case 'TikTok':
      case 'Snapchat':
      case 'Facebook':
      case 'WeChat':
        icons.push('fa fa-square-share-nodes');
        break;
      case 'Electron':
      case 'PhantomJS':
        icons.push('fa fa-code');
        break;
      default:
        icons.push('fa fa-question-circle');
    }

    switch (parser.getDevice().type) {
      case 'mobile':
        icons.push('fa fa-mobile');
        break;
      case 'tablet':
        icons.push('fa fa-tablet');
        break;
      case 'smarttv':
        icons.push('fa fa-tv');
        break;
      case 'console':
        icons.push('fa fa-gamepad');
        break;
      case 'wearable':
        icons.push('fa fa-watch');
        break;
      default:
        icons.push('fa fa-desktop');
    }

    return icons;
  }

  isPublicIp(ipAddress: string) {
    const parts = ipAddress.split('.');
    if (parts.length === 4) {
      return !(
        parts[0] === '10' ||
        (parts[0] === '172' && parseInt(parts[1], 10) >= 16 && parseInt(parts[1], 10) <= 31) ||
        (parts[0] === '192' && parts[1] === '168') ||
        ipAddress === '127.0.0.1'
      );
    }
    return false;
  }

  openUserAgentInfoModal(userAgentString: string, template: TemplateRef<void>) {
    this.processedUA = new UAParser(userAgentString).getResult();
    this.useragentInfoModalRef = this.modalService.show(template);
  }
}
