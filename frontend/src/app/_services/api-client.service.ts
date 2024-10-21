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

  private _maintenanceMode = false;
  private _maintenanceModeInterval: any = undefined;
  public maintenanceModeChanged = new Subject<void>();

  get maintenanceMode(): boolean {
    return this._maintenanceMode;
  }
  set maintenanceMode(value: boolean) {
    if(value && !this._maintenanceMode) {
      //Every 5 seconds, check if maintenance mode is still active
      this._maintenanceModeInterval = setInterval(() => {
        this.get("ping").then(() => {
          console.log("Maintenance mode disabled");
          this.maintenanceMode = false;
          clearInterval(this._maintenanceModeInterval);
        }).catch(() => {});
      }, 10000);
    }
    this._maintenanceMode = value;
    this.maintenanceModeChanged.next();
  }

  private _offline = false;
  private _offlineInterval: any = undefined;
  public offlineChanged = new Subject<void>();

  get offline(): boolean {
    return this._offline;
  }
  set offline(value: boolean) {
    if(value && !this._offline) {
      //Every 5 seconds, check if sill offline
      this._offlineInterval = setInterval(() => {
        this.get("ping").then(() => {
          console.log("Offline mode disabled");
          this.offline = false;
          clearInterval(this._offlineInterval);
        }).catch(() => {});
      }, 10000);
    }
    this._offline = value;
    this.offlineChanged.next();
  }

  constructor(private http: HttpClient) { }

  private returnResponseData(body: any): any {
    if(body === null || body === undefined) return null;
    if(body.data !== undefined) {
      return body.data;
    }
    return body;
  }

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
          resolve(
            this.returnResponseData(v.body)
          );
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
