import { Injectable } from '@angular/core';
import { HttpInterceptor, HttpHandler, HttpRequest, HttpEvent, HttpErrorResponse, HttpXsrfTokenExtractor } from '@angular/common/http';
import { Observable, throwError, retryWhen, timer } from 'rxjs';
import { catchError, mergeMap, finalize } from 'rxjs/operators';
import { Router } from "@angular/router";
import { ApiClientService } from '../_services/api-client.service';

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
    finalize(() => console.log('We are done!'))
  );
};

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  constructor(
    private tokenExtractor: HttpXsrfTokenExtractor,
    private router: Router,
    private api: ApiClientService
  ) { }

  /**
   * This replaces the default Angular XSRF header injector,
   * which is broken for some requests and bad documented.
   * It is disabled for GET/HEAD requests and for every request
   * to an absolute URL. This overwrites the default behavior.
   */
  addXsrfToken(req: HttpRequest<any>): HttpRequest<any> {
    //TODO: check if URL is external to Allerta, if so, don't add XSRF token
    const cookieheaderName = 'X-XSRF-TOKEN';
    let csrfToken = this.tokenExtractor.getToken() as string;
    if (csrfToken !== null && !req.headers.has(cookieheaderName)) {
      req = req.clone({ headers: req.headers.set(cookieheaderName, csrfToken) });
    }
    return req;
  }

  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<Object>> {
    return next.handle(this.addXsrfToken(req)).pipe(
      retryWhen(genericRetryStrategy({
        maxRetryAttempts: 3,
        scalingDuration: 1,
        excludedStatusCodes: [400, 404, 419, 500, 503],
        excludedUrls: ["login", "logout", "me"]
      })),
      catchError(error => {
        console.log(error);
        if (error.status === 419) {
          return new Observable<HttpEvent<Object>>((observer) => {
            this.api.get("csrf-cookie").then(() => {
              next.handle(this.addXsrfToken(req).clone()).subscribe(observer);
            });
          });
        }
        if (error instanceof HttpErrorResponse && !req.url.includes('login') && !req.url.includes('me') && !req.url.includes('logout')) {
          console.log("Error: " + error.status);
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
