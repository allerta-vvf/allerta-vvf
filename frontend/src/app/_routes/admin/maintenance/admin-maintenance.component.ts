import { Component, OnInit } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { ApiClientService } from 'src/app/_services/api-client.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-admin-maintenance',
  templateUrl: './admin-maintenance.component.html',
  styleUrls: ['./admin-maintenance.component.scss']
})
export class AdminMaintenanceComponent implements OnInit {
  public db: any | undefined = undefined;
  public isTableListCollaped = true;

  public jobs: string[] = [];
  //Hard-coded list of jobs that should not be run manually
  public dangerousJobs: string[] = [
    "NotifyUsersManualModeOnJob"
  ];
  public ultraDangerousJobs: string[] = [
    "ResetAvailabilityMinutesJob"
  ];

  public isMaintenanceModeActive = false;

  public telegramBotInfo: any | undefined = undefined;
  public telegramBotInfoArray: any[] = [];

  constructor(
    private translateService: TranslateService,
    private api: ApiClientService
  ) { }

  sizeToHuman(size: number) {
    const i = Math.floor(Math.log(size) / Math.log(1024));
    return (size / Math.pow(1024, i)).toFixed(2) + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
  }

  getDB() {
    this.api.get('admin/db').then((res: any) => {
      this.db = res;
      console.log(this.db);
    }).catch((err: any) => {
      Swal.fire({
        icon: 'error',
        title: this.translateService.instant('error_title'),
        text: err.message
      });
    });
  }

  getJobs() {
    this.api.get('admin/jobs').then((res: any) => {
      this.jobs = res;
      console.log(this.jobs);
    }).catch((err: any) => {
      Swal.fire({
        icon: 'error',
        title: this.translateService.instant('error_title'),
        text: err.message
      });
    });
  }

  getMaintenanceMode() {
    this.api.get('admin/maintenanceMode').then((res: any) => {
      this.isMaintenanceModeActive = res.enabled;
    }).catch((err: any) => {
      Swal.fire({
        icon: 'error',
        title: this.translateService.instant('error_title'),
        text: err.message
      });
    });
  }

  getTelegramBotDebugInfo() {
    this.api.get('admin/telegramBot/debug').then((res: any) => {
      this.telegramBotInfo = res;
      this.telegramBotInfoArray = Object.entries(this.telegramBotInfo);
      console.log(this.telegramBotInfo);
    }).catch((err: any) => {
      Swal.fire({
        icon: 'error',
        title: this.translateService.instant('error_title'),
        text: err.message
      });
    });
  }

  ngOnInit(): void {
    this.getDB();
    this.getJobs();
    this.getMaintenanceMode();
    this.getTelegramBotDebugInfo();
  }

  runMigrations() {
    this.api.post('admin/runMigrations').then((res: any) => {
      Swal.fire({
        icon: 'success',
        title: this.translateService.instant('success_title'),
        text: this.translateService.instant('admin.run_migrations_success')
      });
      this.getDB();
    }).catch((err: any) => {
      Swal.fire({
        icon: 'error',
        title: this.translateService.instant('error_title'),
        text: err.message
      });
    });
  }

  runSeeding() {
    //Require confirmation before proceeding
    Swal.fire({
      title: this.translateService.instant('admin.run_seeding_confirm_title'),
      text: this.translateService.instant('admin.run_seeding_confirm_text'),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: this.translateService.instant('yes'),
      cancelButtonText: this.translateService.instant('no')
    }).then((result) => {
      if (result.isConfirmed) {
        this.api.post('admin/runSeeding').then((res: any) => {
          Swal.fire({
            icon: 'success',
            title: this.translateService.instant('success_title'),
            text: this.translateService.instant('admin.run_seeding_success')
          });
          this.getDB();
        }).catch((err: any) => {
          Swal.fire({
            icon: 'error',
            title: this.translateService.instant('error_title'),
            text: err.message
          });
        });
      }
    });
  }

  runJob(job: string) {
    //Require confirmation before proceeding
    Swal.fire({
      title: this.translateService.instant('admin.run_confirm_title'),
      text: this.translateService.instant('admin.run_confirm_text'),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: this.translateService.instant('yes'),
      cancelButtonText: this.translateService.instant('no')
    }).then((result) => {
      if (result.isConfirmed) {
        this.api.post('admin/runJob', { job }).then((res: any) => {
          Swal.fire({
            icon: 'success',
            title: this.translateService.instant('success_title'),
            text: this.translateService.instant('admin.run_success')
          });
          this.getJobs();
        }).catch((err: any) => {
          Swal.fire({
            icon: 'error',
            title: this.translateService.instant('error_title'),
            text: err.error.message
          });
        });
      }
    });
  }

  updateMaintenanceMode(enabled: boolean) {
    this.api.post('admin/maintenanceMode', { enabled }).then((res: any) => {
      this.isMaintenanceModeActive = enabled;
      if(enabled) {
        //Call res.secret_endpoint to bypass maintenance mode in this session
        this.api.get(res.secret_endpoint).then((res: any) => {
          console.log(res);
        }).catch((err: any) => {
          console.log(err);
        });
      }
      Swal.fire({
        icon: 'success',
        title: this.translateService.instant('success_title'),
        text: this.translateService.instant('admin.maintenance_mode_success')
      });
    }).catch((err: any) => {
      Swal.fire({
        icon: 'error',
        title: this.translateService.instant('error_title'),
        text: err.message
      });
    });
  }

  runOptimization() {
    this.api.post('admin/runOptimization').then((res: any) => {
      Swal.fire({
        icon: 'success',
        title: this.translateService.instant('success_title'),
        text: this.translateService.instant('admin.run_optimization_success')
      });
      this.getDB();
    }).catch((err: any) => {
      Swal.fire({
        icon: 'error',
        title: this.translateService.instant('error_title'),
        text: err.error.message
      });
    });
  }

  clearOptimization() {
    this.api.post('admin/clearOptimization').then((res: any) => {
      Swal.fire({
        icon: 'success',
        title: this.translateService.instant('success_title'),
        text: this.translateService.instant('admin.clear_optimization_success')
      });
      this.getDB();
    }).catch((err: any) => {
      Swal.fire({
        icon: 'error',
        title: this.translateService.instant('error_title'),
        text: err.error.message
      });
    });
  }

  clearCache() {
    this.api.post('admin/clearCache').then((res: any) => {
      Swal.fire({
        icon: 'success',
        title: this.translateService.instant('success_title'),
        text: this.translateService.instant('admin.clear_cache_success')
      });
      this.getDB();
    }).catch((err: any) => {
      Swal.fire({
        icon: 'error',
        title: this.translateService.instant('error_title'),
        text: err.error.message
      });
    });
  }

  setTelegramBotWebhook() {
    this.api.post('admin/telegramBot/setWebhook').then((res: any) => {
      Swal.fire({
        icon: 'success',
        title: this.translateService.instant('success_title'),
        text: this.translateService.instant('admin.telegram_webhook_set_success')
      });
      this.getTelegramBotDebugInfo();
    }).catch((err: any) => {
      Swal.fire({
        icon: 'error',
        title: this.translateService.instant('error_title'),
        text: err.error.message
      });
    });
  }

  unsetTelegramBotWebhook() {
    this.api.post('admin/telegramBot/unsetWebhook').then((res: any) => {
      Swal.fire({
        icon: 'success',
        title: this.translateService.instant('success_title'),
        text: this.translateService.instant('admin.telegram_webhook_unset_success')
      });
      this.getTelegramBotDebugInfo();
    }).catch((err: any) => {
      Swal.fire({
        icon: 'error',
        title: this.translateService.instant('error_title'),
        text: err.error.message
      });
    });
  }
}
