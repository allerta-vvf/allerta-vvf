import { Component } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { AuthService, LoginResponse } from 'src/app/_services/auth.service';
import { GuardLoaderIconService } from 'src/app/_services/guard-loader-icon.service';
import { ApiClientService } from 'src/app/_services/api-client.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent {
  public loading = false;

  public loginResponse: LoginResponse = {loginOk: false, message: ''};
  public username = "";
  public password = "";
  private redirectParamsList: string[] = [];
  private redirectParam = "";
  private extraParam = "";

  constructor(
    public route: ActivatedRoute,
    private router: Router,
    private authService: AuthService,
    private guardLoaderIconService: GuardLoaderIconService,
    public api: ApiClientService
  ) {
    this.route.params.subscribe((params) => {
      if (params["redirect"]) {
        this.redirectParam = params["redirect"];
      }
      if (params["extraParam"]) {
        this.extraParam = params["extraParam"];
      }
    });
    console.log(this.redirectParam);
    console.log(this.extraParam);
    if(this.redirectParam === "") this.router.navigate(['/']);

    this.redirectParamsList = [this.redirectParam];
    if(this.extraParam !== "") this.redirectParamsList.push(this.extraParam);

    if(this.authService.isAuthenticated()) {
      this.router.navigate(this.redirectParamsList);
    } else {
      this.authService.authChanged.subscribe({
        next: () => {
          this.router.navigate(this.redirectParamsList);
        }
      });
    }

    this.guardLoaderIconService.hide();
    this.guardLoaderIconService.lock();
  }

  login(): void {
    this.loading = true;
    this.authService.login(this.username, this.password).then((response: LoginResponse) => {
      this.loginResponse = response;
      this.loading = false;
      console.log(response);
      if (response.loginOk === true) {
        this.guardLoaderIconService.unlock();
        this.router.navigate(this.redirectParamsList);
      }
    });
  }
}