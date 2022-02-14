import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { ApiClientService } from './api-client.service';
import { Observable, Subject } from "rxjs";
import jwt_decode from 'jwt-decode';

export interface LoginResponse {
  loginOk: boolean;
  message: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
    public profile: any = undefined;
    private access_token: string | undefined = undefined;
    public authChanged = new Subject<void>();

    public loadProfile() {
        try{
            console.log("Loading profile", this.access_token);
            let now = Date.now().valueOf() / 1000;
            (window as any).jwt_decode = jwt_decode;
            if(typeof(this.access_token) !== "string") return;
            let decoded: any = jwt_decode(this.access_token);
            if (typeof decoded.exp !== 'undefined' && decoded.exp < now) {
                return false;
            }
            if (typeof decoded.nbf !== 'undefined' && decoded.nbf > now) {
                return false;
            }
            this.profile = decoded.user_info;

            this.profile.hasRole = (role: string) => {
                return Object.values(this.profile.roles).includes(role);
            }

            console.log(this.profile);
            this.authChanged.next();
            return true;
        } catch(e) {
            console.error(e);
            this.removeToken();
            this.profile = undefined;
            return false;
        }
    }

    constructor(private api: ApiClientService, private router: Router) {
        if(localStorage.getItem("access_token") !== null) {
            this.access_token = localStorage.getItem("access_token") as string;
            this.loadProfile();
        }
    }

    public setToken(value: string) {
        localStorage.setItem("access_token", value);
        this.access_token = value;
        this.loadProfile();
    }

    public getToken(): string | undefined {
        return this.access_token;
    }

    private removeToken() {
        this.access_token = '';
        localStorage.removeItem("access_token");
    }

    public isAuthenticated() {
        return this.profile !== undefined;
    }

    public login(username: string, password: string) {
        return new Promise<LoginResponse>((resolve) => {
            this.api.post("login", {
                username: username,
                password: password
            }).then((data: any) => {
                console.log(data);
                this.setToken(data.access_token);
                console.log("Access token", data);
                resolve({
                    loginOk: true,
                    message: data.message
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
        })
    }

    public impersonate(user_id: number): Promise<number> {
        return new Promise((resolve, reject) => {
            console.log("final", user_id);
            this.api.post("impersonate", {
                user_id: user_id
            }).then((response) => {
                this.setToken(response.access_token);
                resolve(user_id);
            }).catch((err) => {
                reject();
            });
        });
    }

    public stop_impersonating(): Promise<number> {
        return new Promise((resolve, reject) => {
            this.api.post("stop_impersonating").then((response) => {
                this.setToken(response.access_token);
                resolve(response.user_id);
            }).catch((err) => {
                reject();
            });
        });
    }

    public logout(routerDestination?: string[] | undefined) {
        if(this.profile.impersonating_user) {
            this.stop_impersonating().then((user_id) => {
            });
        } else {
            this.removeToken();
            this.profile = undefined;
            if(routerDestination === undefined) {
                routerDestination = ["login", "list"];
            }
            this.router.navigate(routerDestination);
        }
    }

    public refreshToken() {
        return new Observable<string>((observer) => {
            this.api.post("refreshToken").then((data: any) => {
                this.setToken(data.token);
                observer.next(data.token);
                observer.complete();
            }).catch((err) => {
                observer.error(err);
            });
        });
    }
}
