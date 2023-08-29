import { Injectable } from '@angular/core';
import { HttpInterceptor, HttpHandler, HttpRequest, HttpEvent, HttpErrorResponse } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { Router } from "@angular/router";

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  constructor(private router: Router) { }

  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<Object>> {
    return next.handle(req).pipe(catchError(error => {
      console.log(error);
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
