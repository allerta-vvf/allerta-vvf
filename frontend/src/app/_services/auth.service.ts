import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { ApiClientService } from './api-client.service';
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
    private access_token = '';

    public loadProfile() {
        try{
            console.log("Loading profile", this.access_token);
            let now = Date.now().valueOf() / 1000;
            (window as any).jwt_decode = jwt_decode;
            let decoded: any = jwt_decode(this.access_token);
            if (typeof decoded.exp !== 'undefined' && decoded.exp < now) {
                return false;
            }
            if (typeof decoded.nbf !== 'undefined' && decoded.nbf > now) {
                return false;
            }
            this.profile = decoded.user_info;

            console.log(this.profile);
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
            this.api.setToken(this.access_token);
            this.loadProfile();
        }
    }

    private setToken(value: string) {
        localStorage.setItem("access_token", value);
        this.access_token = value;
        this.api.setToken(this.access_token);
        this.loadProfile();
    }

    private removeToken() {
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

    public logout(routerDestination?: string[] | undefined) {
        this.removeToken();
        this.profile = undefined;
        if(routerDestination === undefined) {
            routerDestination = ["login", "list"];
        }
        this.router.navigate(routerDestination);
    }
}
