import { Component } from '@angular/core';
import { AuthService } from './_services/auth.service';
import { LocationBackService } from 'src/app/_services/locationBack.service';
import { GuardLoaderIconService } from 'src/app/_services/guard-loader-icon.service';
import { versions } from 'src/environments/versions';
import { Router, RouteConfigLoadStart, RouteConfigLoadEnd } from '@angular/router';
import { ApiClientService } from './_services/api-client.service';
import { ModalAlertComponent } from 'src/app/_components/modal-alert/modal-alert.component';
import { BsModalService } from 'ngx-bootstrap/modal';
import { AuthorizeGuard } from './_guards/authorize.guard';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent {
  public menuButtonClicked = false;
  public revision_datetime_string;
  public versions = versions;
  public loadingRoute = false;
  private loadAlertsInterval: NodeJS.Timer | undefined = undefined;
  public alerts = [];

  constructor(
    public auth: AuthService,
    private locationBackService: LocationBackService,
    public guardLoaderIconService: GuardLoaderIconService,
    private router: Router,
    private api: ApiClientService,
    private modalService: BsModalService,
    public guard: AuthorizeGuard
  ) {
    this.revision_datetime_string = new Date(versions.revision_timestamp).toLocaleString(undefined,  { day: '2-digit', month: '2-digit', year: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric' });
    this.locationBackService.initialize();
  }

  loadAlerts() {
    if(this.auth.profile) {
      this.api.get("alerts").then((response) => {
        this.alerts = response;
      });
    }
  }

  ngOnInit () {
    this.router.events.subscribe((event) => {
      if (event instanceof RouteConfigLoadStart) {
        this.loadingRoute = true;
      } else if (event instanceof RouteConfigLoadEnd) {
        this.loadingRoute = false;
      }
    });

    /*
    this.loadAlertsInterval = setInterval(() => {
      console.log("Refreshing alerts...");
      this.loadAlerts();
    }, 15000);
    this.loadAlerts();

    this.api.alertsChanged.subscribe(() => {
      this.loadAlerts();
    });
    */
  }

  openAlert(id: number) {
    this.modalService.show(ModalAlertComponent, {
      initialState: {
        id: id
      }
    });
  }
}
