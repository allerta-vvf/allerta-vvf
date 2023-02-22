import { Injectable } from '@angular/core';
import { HttpInterceptor, HttpHandler, HttpRequest, HttpEvent, HttpErrorResponse } from '@angular/common/http';
import { AuthService } from '../_services/auth.service';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  constructor(/*private auth: AuthService*/) { }

  //TODO: fix interceptor and logout (client-side only) if 401 error

  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<Object>> {
    return next.handle(req);
    /*
    return next.handle(req).pipe(catchError(error => {
      console.log(error);
      if (error instanceof HttpErrorResponse && !req.url.includes('login') && !req.url.includes('me') && !req.url.includes('logout')) {
        if(error.status === 400) {
          this.auth.logout();
          return throwError(() => error);
        } else if (error.status === 401) {
          this.auth.logout();
        }
      } 
      return throwError(() => error);
    }));
    */
  }
}
