import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { ApiClientService } from './api-client.service';
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
        can: (permission: string) => false
    };
    public profile: any = this.defaultPlaceholderProfile;
    public authChanged = new Subject<void>();
    public authLoaded = false;

    public loadProfile() {
        console.log("Loading profile data...");
        return new Promise<void>((resolve, reject) => {
            this.api.post("me").then((data: any) => {
                this.profile = data;
    
                this.profile.can = (permission: string) => {
                    return this.profile.permissions.includes(permission);
                }

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

    constructor(private api: ApiClientService, private router: Router) {
        this.loadProfile().then(() => {
            console.log("User is authenticated");
        }).catch(() => {
            console.log("User is not logged in");
        }).finally(() => {
            this.authLoaded = true;
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
                    password: password
                }).then((data: any) => {
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
                    if(err.status === 401) {
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
            });
        })
    }

    public impersonate(user_id: number): Promise<void> {
        return new Promise((resolve, reject) => {
            this.api.post(`impersonate/${user_id}`).then(() => {
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
                reject();
            });
        });
    }

    public stop_impersonating(): Promise<void> {
        return new Promise((resolve, reject) => {
            this.api.post("stop_impersonating").then(() => {
                Sentry.setUser(null);
                resolve();
            }).catch((err) => {
                console.error(err);
                reject();
            });
        });
    }

    public logout(routerDestination?: string[] | undefined) {
        if(this.profile.impersonating_user) {
            this.stop_impersonating().then(() => {
                this.loadProfile();
            });
        } else {
            this.api.post("logout").then((data: any) => {
                this.profile = this.defaultPlaceholderProfile;
                if(routerDestination === undefined) {
                    routerDestination = ["login", "list"];
                }
                Sentry.setUser(null);
                this.router.navigate(routerDestination);
            });
        }
    }
}
