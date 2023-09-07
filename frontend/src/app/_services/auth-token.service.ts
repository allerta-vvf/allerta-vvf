import { Injectable } from '@angular/core';

@Injectable({
    providedIn: 'root'
})
export class AuthTokenService {
    private token = "";

    constructor() {
        //Load auth token from local storage
        this.token = localStorage.getItem('token') || '';
    }

    public updateToken(token: string) {
        if(token == null || token == undefined) token = '';
        this.token = token;
        localStorage.setItem('token', token);
    }

    public getToken(): string {
        return this.token;
    }

    public clearToken() {
        this.token = '';
        localStorage.removeItem('token');
    }
}
