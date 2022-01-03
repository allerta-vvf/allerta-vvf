import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams, HttpInterceptor, HttpRequest, HttpHandler, HttpEvent, HttpErrorResponse } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { AuthService } from './auth.service';

@Injectable({
  providedIn: 'root'
})
export class ApiClientService {
  private apiRoot = 'api/';
  public requestOptions = {};

  constructor(private http: HttpClient) {
    this.requestOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    }
  }

  public setToken(token: string) {
    this.requestOptions = {
      headers: new HttpHeaders({
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    }
  }

  public apiEndpoint(endpoint: string): string {
    return this.apiRoot + endpoint;
  }

  public dataToParams(data: any): string {
    return Object.keys(data).reduce(function (params, key) {
      if(typeof data[key] === 'object') {
        data[key] = JSON.stringify(data[key]);
      }
      params.set(key, data[key]);
      return params;
    }, new URLSearchParams()).toString();
  }

  public get(endpoint: string) {
    return new Promise<any>((resolve, reject) => {
      this.http.get(this.apiEndpoint(endpoint), this.requestOptions).subscribe((data: any) => {
        resolve(data);
      }, (err) => {
        reject(err);
      });
    });
  }

  public post(endpoint: string, data: any) {
    return new Promise<any>((resolve, reject) => {
      this.http.post(this.apiEndpoint(endpoint), this.dataToParams(data), this.requestOptions).subscribe((data: any) => {
        resolve(data);
      }, (err) => {
        reject(err);
      });
    });
  }

  public put(endpoint: string, data: any) {
    return new Promise<any>((resolve, reject) => {
      this.http.put(this.apiEndpoint(endpoint), this.dataToParams(data), this.requestOptions).subscribe((data: any) => {
        resolve(data);
      }, (err) => {
        reject(err);
      });
    });
  }

  public delete(endpoint: string) {
    return new Promise<any>((resolve, reject) => {
      this.http.delete(this.apiEndpoint(endpoint), this.requestOptions).subscribe((data: any) => {
        resolve(data);
      }, (err) => {
        reject(err);
      });
    });
  }
}
 
@Injectable()
export class UnauthorizedInterceptor implements HttpInterceptor {
  constructor(private auth: AuthService) { }

  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    return next.handle(request).pipe( tap({
      next: () => {},
      error: (err: any) => {
      if (err instanceof HttpErrorResponse) {
        if (err.status !== 401) {
         return;
        }
        console.log("Login richiesto");
        this.auth.logout();
        //this.router.navigate(['login']);
      }
    }}));
  }
}
