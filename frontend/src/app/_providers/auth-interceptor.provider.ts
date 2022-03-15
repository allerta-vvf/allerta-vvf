import { Injectable } from '@angular/core';
import { HttpInterceptor, HttpHandler, HttpRequest, HttpEvent, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { AuthService } from '../_services/auth.service';
import { BehaviorSubject, Observable, throwError } from 'rxjs';
import { catchError, filter, switchMap, take } from 'rxjs/operators';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  private isRefreshing = false;
  private refreshTokenSubject: BehaviorSubject<string|undefined> = new BehaviorSubject<string|undefined>(undefined);

  constructor(private auth: AuthService) { }

  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<Object>> {
    const token = this.auth.getToken();
    let authReq = this.addHeaders(req, token);

    return next.handle(authReq).pipe(catchError(error => {
      if (error instanceof HttpErrorResponse && !authReq.url.includes('login')) {
        if(error.status === 400) {
          return this.handle400Error(authReq, next);
        } else if (error.status === 401) {
          this.auth.logout();
        }
      } 
      return throwError(() => new Error(error));
    }));
  }

  private handle400Error(request: HttpRequest<any>, next: HttpHandler) {
    if (!this.isRefreshing) {
      this.isRefreshing = true;
      this.refreshTokenSubject.next(undefined);
      return this.auth.refreshToken().pipe(
          switchMap((token: string) => {
            this.isRefreshing = false;
            this.refreshTokenSubject.next(token);

            return next.handle(this.addHeaders(request, token));
          }),
          catchError((err) => {
            this.isRefreshing = false;
            this.auth.logout();
            return throwError(() => new Error(err));
          })
      );
    }
    return this.refreshTokenSubject.pipe(
      filter(token => token !== undefined),
      take(1),
      switchMap((token) => {
        return next.handle(this.addHeaders(request, token));
      })
    );
  }

  private addHeaders(request: HttpRequest<any>, token: string|undefined) {
    if (typeof token === 'string' && token.length > 10) {
      const headers = new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded',
        'Authorization': `Bearer ${token}`
      });
      return request.clone({ headers });
    } else {
      const headers = new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      });
      return request.clone({ headers });
    }
  }

}
