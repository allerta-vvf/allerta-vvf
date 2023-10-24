import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Subject } from "rxjs";

@Injectable({
  providedIn: 'root'
})
export class ApiClientService {
  private apiRoot = 'api/';

  public lastEtag = "";
  public isLastSame = false;

  public alertsChanged = new Subject<void>();
  public availableUsers: undefined | number = undefined;

  constructor(private http: HttpClient) { }

  public apiEndpoint(endpoint: string): string {
    if(endpoint.startsWith('http') || endpoint.startsWith('//')) {
      return endpoint;
    }
    return this.apiRoot + endpoint;
  }

  public get(endpoint: string, data: any = {}, etag: string = "") {
    if(etag === null) etag = "";
    return new Promise<any>((resolve, reject) => {
      this.http.get(this.apiEndpoint(endpoint), {
        params: new HttpParams({ fromObject: data }),
        observe: 'response',
        headers: etag !== "" ? {
          'If-None-Match': etag
        } : {}
      }).subscribe({
        next: (v: any) => {
          this.lastEtag = v.headers.get("etag");
          this.isLastSame = etag === this.lastEtag && etag !== "";
          resolve(v.body);
        },
        error: (e) => reject(e)
      });
    });
  }

  public post(endpoint: string, data: any = {}) {
    return new Promise<any>((resolve, reject) => {
      this.http.post(this.apiEndpoint(endpoint), data).subscribe({
        next: (v) => resolve(v),
        error: (e) => reject(e)
      });
    });
  }

  public put(endpoint: string, data: any = {}) {
    return new Promise<any>((resolve, reject) => {
      this.http.put(this.apiEndpoint(endpoint), data).subscribe({
        next: (v) => resolve(v),
        error: (e) => reject(e)
      });
    });
  }

  public patch(endpoint: string, data: any = {}) {
    return new Promise<any>((resolve, reject) => {
      this.http.patch(this.apiEndpoint(endpoint), data).subscribe({
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
