import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Subject } from "rxjs";

@Injectable({
  providedIn: 'root'
})
export class ApiClientService {
  private apiRoot = 'api/';

  public alertsChanged = new Subject<void>();
  public availableUsers: undefined | number = undefined;

  constructor(private http: HttpClient) { }

  public apiEndpoint(endpoint: string): string {
    if(endpoint.startsWith('https')) {
      return endpoint;
    }
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

  public get(endpoint: string, data: any = {}) {
    return new Promise<any>((resolve, reject) => {
      this.http.get(this.apiEndpoint(endpoint), {
        params: new HttpParams({ fromObject: data })
      }).subscribe({
        next: (v) => resolve(v),
        error: (e) => reject(e)
      });
    });
  }

  public post(endpoint: string, data: any = {}) {
    return new Promise<any>((resolve, reject) => {
      this.http.post(this.apiEndpoint(endpoint), this.dataToParams(data)).subscribe({
        next: (v) => resolve(v),
        error: (e) => reject(e)
      });
    });
  }

  public put(endpoint: string, data: any = {}) {
    return new Promise<any>((resolve, reject) => {
      this.http.put(this.apiEndpoint(endpoint), this.dataToParams(data)).subscribe({
        next: (v) => resolve(v),
        error: (e) => reject(e)
      });
    });
  }

  public delete(endpoint: string) {
    return new Promise<any>((resolve, reject) => {
      this.http.delete(this.apiEndpoint(endpoint)).subscribe({
        next: (v) => resolve(v),
        error: (e) => reject(e)
      });
    });
  }
}
