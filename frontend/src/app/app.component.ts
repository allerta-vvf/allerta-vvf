import { Component } from '@angular/core';
import { AuthService } from './_services/auth.service';
import { LocationBackService } from 'src/app/_services/locationBack.service';
import { versions } from 'src/environments/versions';
import { Router, RouteConfigLoadStart, RouteConfigLoadEnd } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';

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

  constructor(
    public auth: AuthService,
    private locationBackService: LocationBackService,
    private router: Router,
    private translate: TranslateService
  ) {
    this.revision_datetime_string = new Date(versions.revision_timestamp).toLocaleString(undefined,  { day: '2-digit', month: '2-digit', year: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric' });
    this.translate.setDefaultLang('en');
    this.translate.use("it");
  }

  ngOnInit () {
    this.router.events.subscribe((event) => {
      if (event instanceof RouteConfigLoadStart) {
        this.loadingRoute = true;
      } else if (event instanceof RouteConfigLoadEnd) {
        this.loadingRoute = false;
      }
    });
  }
}
