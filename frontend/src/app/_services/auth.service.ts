import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { ApiClientService } from './api-client.service';
import { Subject } from "rxjs";

export interface LoginResponse {
  loginOk: boolean;
  message: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
    public profile: any = undefined;
    public authChanged = new Subject<void>();
    public authLoaded = false;

    public loadProfile() {
        console.log("Loading profile data...");
        return new Promise<void>((resolve, reject) => {
            this.api.post("me").then((data: any) => {
                this.profile = data;
    
                this.profile.hasRole = (role: string) => {
                    return true;
                }
    
                this.authChanged.next();
                resolve();
            }).catch((e) => {
                console.error(e);
                this.profile = undefined;
                reject();
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
        return this.profile !== undefined;
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

    public impersonate(user_id: number): Promise<number> {
        return new Promise((resolve, reject) => {
            resolve(0);
        });
    }

    public logout(routerDestination?: string[] | undefined) {
        this.api.post("logout").then((data: any) => {
            this.profile = undefined;
            if(routerDestination === undefined) {
                routerDestination = ["login", "list"];
            }
            this.router.navigate(routerDestination);
        });
        /*
        if(this.profile.impersonating_user) {
            this.stop_impersonating().then((user_id) => {
            });
        } else {
            this.profile = undefined;
            if(routerDestination === undefined) {
                routerDestination = ["login", "list"];
            }
            this.router.navigate(routerDestination);
        }
        */
    }
}
