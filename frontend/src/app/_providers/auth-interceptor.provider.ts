import { Injectable } from '@angular/core';
import { HttpInterceptor, HttpHandler, HttpRequest, HttpEvent, HttpErrorResponse, HttpXsrfTokenExtractor, HttpResponse } from '@angular/common/http';
import { Observable, throwError, retryWhen, timer } from 'rxjs';
import { catchError, mergeMap, finalize } from 'rxjs/operators';
import { Router } from "@angular/router";
import { ApiClientService } from '../_services/api-client.service';
import { AuthTokenService } from '../_services/auth-token.service';

//https://stackoverflow.com/a/58394106
const genericRetryStrategy = ({
  maxRetryAttempts = 3,
  scalingDuration = 1000,
  excludedStatusCodes = [],
  excludedUrls = []
}: {
  maxRetryAttempts?: number,
  scalingDuration?: number,
  excludedStatusCodes?: number[],
  excludedUrls?: string[]
} = {}) => (attempts: Observable<any>) => {
  return attempts.pipe(
    mergeMap((error, i) => {
      const retryAttempt = i + 1;
      // if maximum number of retries have been met
      // or response is a status code we don't wish to retry, throw error
      if (
        retryAttempt > maxRetryAttempts ||
        excludedStatusCodes.find(e => e === error.status) ||
        excludedUrls.find((e: string) => error.url.includes(e))
      ) {
        return throwError(() => error);
      }
      console.log(
        `Attempt ${retryAttempt}: retrying in ${retryAttempt *
          scalingDuration}ms`
      );
      // retry after 1s, 2s, etc...
      return timer(retryAttempt * scalingDuration);
    }),
    //finalize(() => console.log('We are done!'))
  );
};

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  constructor(
    private tokenExtractor: HttpXsrfTokenExtractor,
    private router: Router,
    private api: ApiClientService,
    private authToken: AuthTokenService
  ) { }

  /**
   * This replaces the default Angular XSRF header injector,
   * which is broken for some requests and bad documented.
   * It is disabled for GET/HEAD requests and for every request
   * to an absolute URL. This overwrites the default behavior.
   */
  addXsrfToken(req: HttpRequest<any>): HttpRequest<any> {
    const cookieheaderName = 'X-XSRF-TOKEN';
    let csrfToken = this.tokenExtractor.getToken() as string;
    if (csrfToken !== null && !req.headers.has(cookieheaderName)) {
      req = req.clone({ headers: req.headers.set(cookieheaderName, csrfToken) });
    }
    return req;
  }

  addBearerToken(req: HttpRequest<any>): HttpRequest<any> {
    if (this.authToken.getToken() !== null && !req.headers.has('Authorization')) {
      req = req.clone({ headers: req.headers.set('Authorization', 'Bearer ' + this.authToken.getToken()) });
    }
    return req;
  }

  updateRequest(req: HttpRequest<any>): HttpRequest<any> {
    //If request is absolute, don't add XSRF token or Bearer token
    if (
      req.url.startsWith('http') ||
      req.url.startsWith('//') ||
      req.url.includes('/assets/')
    ) return req;

    req = this.addXsrfToken(req);
    if(this.authToken.getToken() !== '') req = this.addBearerToken(req);
    return req;
  }

  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<Object>> {
    //If maintenance mode is enabled, return 503 except for ping
    if (this.api.maintenanceMode && !req.url.includes("ping")) {
      return new Observable<HttpEvent<Object>>((observer) => {
        observer.next(new HttpResponse({
          body: { message: "Maintenance mode" },
          status: 503,
          statusText: "Service Unavailable",
          url: req.url
        }));
        observer.complete();
      });
    }
    //If offline, return 504 except for ping
    if (this.api.offline && !req.url.includes("ping")) {
      return new Observable<HttpEvent<Object>>((observer) => {
        observer.next(new HttpResponse({
          body: { message: "Offline" },
          status: 504,
          statusText: "Gateway Timeout",
          url: req.url
        }));
        observer.complete();
      });
    }
    return next.handle(this.updateRequest(req)).pipe(
      retryWhen(genericRetryStrategy({
        maxRetryAttempts: 3,
        scalingDuration: 1,
        excludedStatusCodes: [304, 400, 404, 419, 500, 503],
        excludedUrls: ["login", "logout", "me", "impersonate", "stop_impersonating", "ping"]
      })),
      catchError(error => {
        if (error.status === 304) {
          //Return current response as successfully
          return new Observable<HttpEvent<Object>>((observer) => {
            observer.next(new HttpResponse({
              body: error.error,
              headers: error.headers,
              status: error.status,
              statusText: error.statusText,
              url: error.url
            }));
            observer.complete();
          });
        } else if (error.status === 503) {
          this.api.maintenanceMode = true;
          return throwError(() => error);
        } else if (error.status === 504) {
          this.api.offline = true;
          return throwError(() => error);
        } else if (error.status === 419) {
          return new Observable<HttpEvent<Object>>((observer) => {
            this.api.get("csrf-cookie").then(() => {
              next.handle(this.updateRequest(req).clone()).subscribe(observer);
            });
          });
        }
        if (error instanceof HttpErrorResponse && !req.url.includes('login') && !req.url.includes('me') && !req.url.includes('logout')) {
          if (error.status === 400) {
            this.router.navigate(["logout"]);
            return throwError(() => error);
          } else if (error.status === 401) {
            this.router.navigate(["logout"]);
          }
        }
        return throwError(() => error);
      }));
  }
}
