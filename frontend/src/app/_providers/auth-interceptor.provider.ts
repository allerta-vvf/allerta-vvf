import { Injectable } from '@angular/core';
import { HttpInterceptor, HttpHandler, HttpRequest, HttpEvent, HttpErrorResponse, HttpXsrfTokenExtractor } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { Router } from "@angular/router";
import { ApiClientService } from '../_services/api-client.service';

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
    return next.handle(this.addXsrfToken(req)).pipe(catchError(error => {
      console.log(error);
      if(error.status === 419) {
        return new Observable<HttpEvent<Object>>((observer) => {
          this.api.get("csrf-cookie").then(() => {
            next.handle(this.addXsrfToken(req).clone()).subscribe(observer);
          });
        });
      }
      if (error instanceof HttpErrorResponse && !req.url.includes('login') && !req.url.includes('me') && !req.url.includes('logout')) {
        if(error.status === 400) {
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
