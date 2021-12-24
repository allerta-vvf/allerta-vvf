import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class ApiClientService {
  private apiRoot = '/api/';
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
    let params = new HttpParams({
      fromObject: data,
    });
    return new Promise<any>((resolve, reject) => {
      this.http.post(this.apiEndpoint(endpoint), params.toString(), this.requestOptions).subscribe((data: any) => {
        resolve(data);
      }, (err) => {
        reject(err);
      });
    });
  }

  public put(endpoint: string, data: any) {
    let params = new HttpParams({
      fromObject: data,
    });
    return new Promise<any>((resolve, reject) => {
      this.http.put(this.apiEndpoint(endpoint), params.toString(), this.requestOptions).subscribe((data: any) => {
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