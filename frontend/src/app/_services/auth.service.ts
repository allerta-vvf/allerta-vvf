import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { ApiClientService } from './api-client.service';
import { AuthTokenService } from './auth-token.service';
import { Subject } from "rxjs";
import * as Sentry from "@sentry/angular-ivy";

export interface LoginResponse {
  loginOk: boolean;
  message: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private defaultPlaceholderProfile: any = {
    id: undefined,
    impersonating: false,
    options: {},
    can: (permission: string) => false,
    getOption: (option: string, defaultValue: string) => defaultValue,
  };
  public profile: any = this.defaultPlaceholderProfile;
  public authChanged = new Subject<void>();
  public _authLoaded = false;

  public loadProfile() {
    console.log("Loading profile data...");
    return new Promise<void>((resolve, reject) => {
      this.api.post("me").then((data: any) => {
        this.profile = data;
        this.profile.options = this.profile.options.reduce((acc: any, val: any) => {
          acc[val.name] = val.value;
          return acc;
        }, {});

        this.profile.can = (permission: string) => {
          return this.profile.permissions.includes(permission);
        }

        this.profile.getOption = (option: string, defaultValue: any) => {
          let value = this.profile.options[option];
          if (value === undefined) {
            return defaultValue;
          } else {
            return value;
          }
        }

        this.profile.profilePageLink = "/users/" + this.profile.id;

        Sentry.setUser({
          id: this.profile.id,
          name: this.profile.name
        });

        resolve();
      }).catch((e) => {
        console.error(e);
        this.profile = this.defaultPlaceholderProfile;
        reject();
      }).finally(() => {
        this.authChanged.next();
      });
    });
  }

  authLoaded() {
    return this._authLoaded;
  }

  constructor(
    private api: ApiClientService,
    private authToken: AuthTokenService,
    private router: Router
  ) {
    this.loadProfile().then(() => {
      console.log("User is authenticated");
    }).catch(() => {
      console.log("User is not logged in");
    }).finally(() => {
      this._authLoaded = true;
    });
  }

  public isAuthenticated() {
    return this.profile.id !== undefined;
  }

  public login(username: string, password: string) {
    return new Promise<LoginResponse>((resolve) => {
      this.api.get("csrf-cookie").then((data: any) => {
        this.api.post("login", {
          username: username,
          password: password,
          // use_sessions: true //Disabled because on cheap hosting it can cause problems
        }).then((data: any) => {
          this.authToken.updateToken(data.access_token);
          this.loadProfile().then(() => {
            resolve({
              loginOk: true,
              message: data.message
            });
          }).catch(() => {
            resolve({
              loginOk: false,
              message: "Unknown error"
            });
          });
        }).catch((err) => {
          let error_message = "";
          if (err.status === 401 || err.status === 422) {
            error_message = err.error.message;
          } else if (err.status === 400) {
            let error_messages = err.error.errors;
            error_message = error_messages.map((val: any) => {
              return `${val.msg} in ${val.param}`;
            }).join(" & ");
          } else if (err.status === 500) {
            error_message = "Server error";
          } else {
            error_message = "Unknown error";
          }
          resolve({
            loginOk: false,
            message: error_message
          });
        });
      }).catch((err) => {
        if (err.status = 500) {
          resolve({
            loginOk: false,
            message: "Server error"
          });
        } else {
          resolve({
            loginOk: false,
            message: "Unknown error"
          });
        }
      });
    })
  }

  public impersonate(user_id: number): Promise<void | string> {
    return new Promise((resolve, reject) => {
      this.api.post(`impersonate/${user_id}`).then((data) => {
        this.authToken.updateToken(data.access_token);
        this.loadProfile().then(() => {
          resolve();
        }).catch((err) => {
          console.error(err);
          this.logout();
          this.profile.impersonating_user = false;
          this.logout();
        });
      }).catch((err) => {
        console.error(err);
        reject(err.error.message);
      });
    });
  }

  public stop_impersonating(): Promise<void> {
    return new Promise((resolve, reject) => {
      this.api.post("stop_impersonating").then((data) => {
        this.authToken.updateToken(data.access_token);
        this.api.post("refresh_token").then((data) => {
          this.authToken.updateToken(data.access_token);
          Sentry.setUser(null);
          resolve();
        }).catch((err) => {
          this.logout(undefined, true);
          reject();
        });
      }).catch((err) => {
        this.logout(undefined, true);
        reject();
      });
    });
  }

  public logout(routerDestination?: string[] | undefined, forceLogout: boolean = false) {
    if (!forceLogout && this.profile.impersonating_user) {
      this.stop_impersonating().then(() => {
        this.loadProfile();
      }).catch((err) => {
        console.error(err);
      });
    } else {
      this.api.post("logout").then((data: any) => {
        this.profile = this.defaultPlaceholderProfile;
        if (routerDestination === undefined) {
          routerDestination = ["login", "list"];
        }
        this.authToken.clearToken();
        Sentry.setUser(null);
        this.router.navigate(routerDestination);
      });
    }
  }
}
