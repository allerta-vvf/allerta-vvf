import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class ApiClientService {
  private apiRoot = '/api/';
  public requestOptions = {};

  constructor(private http: HttpClient) { }

  public setToken(token: string) {
    this.requestOptions = {
      headers: {
        Authorization: `Bearer ${token}`
      }
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

  public post(endpoint: string, body: any) {
    return new Promise<any>((resolve, reject) => {
      this.http.post(this.apiEndpoint(endpoint), body, this.requestOptions).subscribe((data: any) => {
        resolve(data);
      }, (err) => {
        reject(err);
      });
    });
  }

  public put(endpoint: string, body: any) {
    return new Promise<any>((resolve, reject) => {
      this.http.put(this.apiEndpoint(endpoint), body, this.requestOptions).subscribe((data: any) => {
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