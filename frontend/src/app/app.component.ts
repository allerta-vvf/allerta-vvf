import { Component } from '@angular/core';
import { AuthService } from './_services/auth.service';
import { versions } from 'src/environments/versions';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss']
})
export class AppComponent {
  public menuButtonClicked = false;
  public revision_datetime_string;
  public versions = versions;

  constructor(public auth: AuthService) {
    this.revision_datetime_string = new Date(versions.revision_timestamp).toLocaleString(undefined,  { day: '2-digit', month: '2-digit', year: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric' });
  }
}
