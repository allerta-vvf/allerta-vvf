import { Component, OnInit, OnDestroy, Input, Output, EventEmitter } from '@angular/core';
import { Router } from '@angular/router';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { AuthService } from '../../_services/auth.service';
import { ToastrService } from 'ngx-toastr';
import { TranslateService } from '@ngx-translate/core';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-table',
  templateUrl: './table.component.html',
  styleUrls: ['./table.component.scss']
})
export class TableComponent implements OnInit, OnDestroy {

  @Input() sourceType?: string;
  @Input() refreshInterval?: number;

  @Output() changeAvailability: EventEmitter<{user: number, newState: 0|1}> = new EventEmitter<{user: number, newState: 0|1}>();
  @Output() userImpersonate: EventEmitter<number> = new EventEmitter<number>();

  public data: any = [];

  public loadDataInterval: NodeJS.Timer | undefined = undefined;

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

  loadTableData() {
    if(!this.sourceType) this.sourceType = "list";
    this.api.get(this.sourceType).then((data: any) => {
      console.log(data);
      this.data = data.filter((row: any) => typeof row.hidden !== 'undefined' ? !row.hidden : true);
      if(this.sourceType === 'list') {
        this.api.availableUsers = this.data.filter((row: any) => row.available).length;
      }
    });
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
    if(this.auth.profile.hasRole('SUPER_EDITOR')) {
      this.changeAvailability.emit({user, newState});
    }
  }

  onUserImpersonate(user: number) {
    if(this.auth.profile.hasRole('SUPER_ADMIN')) {
      this.auth.impersonate(user).then((user_id) => {
        this.loadTableData();
        this.userImpersonate.emit(user_id);
      });
    }
  }

  openPlaceDetails(lat: number, lng: number) {
    this.router.navigate(['/place-details', lat, lng]);
  }

  editService(id: number) {
    this.router.navigate(['/services', id]); 
  }

  deleteService(id: number) {
    console.log(id);
    this.translate.get(['table.yes_remove', 'table.cancel', 'table.remove_service_confirm', 'table.remove_service_text']).subscribe((res: { [key: string]: string; }) => {
      console.log(res);
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
            this.translate.get('table.service_removed_successfully').subscribe((res: string) => {
              this.toastr.success(res);
            });
            this.loadTableData();
          }).catch((e) => {
            this.translate.get('table.service_removed_error').subscribe((res: string) => {
              this.toastr.error(res);
            });
          });
        }
      });
    });
  }
}
