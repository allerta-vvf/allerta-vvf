import { Injectable } from '@angular/core';
import { HttpInterceptor, HttpRequest, HttpHandler, HttpEvent, HttpErrorResponse } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { AuthService } from '../_services/auth.service';

@Injectable()
export class UnauthorizedInterceptor implements HttpInterceptor {
  constructor(private auth: AuthService) { }

  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    return next.handle(request).pipe( tap({
      next: () => {},
      error: (err: any) => {
      if (err instanceof HttpErrorResponse) {
        if (err.status !== 401 || request.url.includes('/login')) {
          return;
        }
        console.log("Login required");
        this.auth.logout();
      }
    }}));
  }
}
