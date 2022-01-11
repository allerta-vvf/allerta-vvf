import { Component, OnInit, OnDestroy, Input, Output, EventEmitter } from '@angular/core';
import { Router } from '@angular/router';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { AuthService } from '../../_services/auth.service';
import { ToastrService } from 'ngx-toastr';
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

  public data: any = [];

  public loadDataInterval: NodeJS.Timer | undefined = undefined;

  constructor(
    private api: ApiClientService,
    public auth: AuthService,
    private router: Router,
    private toastr: ToastrService
  ) { }

  getTime() {
    return Math.floor(Date.now() / 1000);
  }

  loadTableData() {
    this.api.get(this.sourceType || "list").then((data: any) => {
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
      if(typeof (window as any).skipTableReload !== 'undefined' && (window as any).skipTableReload) {
        return;
      }
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

  openPlaceDetails(lat: number, lng: number) {
    this.router.navigate(['/place-details', lat, lng]);
  }

  deleteService(id: number) {
    console.log(id);
    Swal.fire({
      title: 'Sei del tutto sicuro di voler rimuovere l\'intervento?',
      text: "Gli interventi eliminati non si possono recuperare.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Si, rimuovilo',
      cancelButtonText: 'Annulla'
    }).then((result) => {
      if (result.isConfirmed) {
        this.api.delete(`services/${id}`).then((response) => {
          this.toastr.success('Intervento rimosso con successo.');
          this.loadTableData();
        }).catch((e) => {
          this.toastr.error('Errore durante la rimozione dell\'intervento.');
        });
      }
    })
  }
}
