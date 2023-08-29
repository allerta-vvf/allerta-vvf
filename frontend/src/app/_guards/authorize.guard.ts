import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, Router, RouterStateSnapshot, UrlTree } from '@angular/router';
import { Observable, Subject } from 'rxjs';
import { debounceTime } from 'rxjs/operators';
import { AuthService } from '../_services/auth.service';

@Injectable({
  providedIn: 'root'
})
export class AuthorizeGuard implements CanActivate {
  //https://stackoverflow.com/a/48848557
  public loader$ = new Subject<boolean>();
  public loader = false;

  constructor(private authService: AuthService, private router: Router) {
    this.loader$.pipe(
      debounceTime(250)
    ).subscribe((loader) => {
      this.loader = loader;
    });
  }

  checkAuthAndRedirect(
    route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ): boolean {
    console.log(this.authService, route, state);
    if(this.authService.profile.id === undefined) {
      console.log("not logged in");
      this.router.navigate(['login', state.url.replace('/', '')]);
      return false;
    } else {
      return true;
    }
  }

  canActivate(
    route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ): Observable<boolean | UrlTree> | Promise<boolean | UrlTree> | boolean | UrlTree {
    if(this.authService.authLoaded) {
      this.loader$.next(false);
      console.log("Auth already loaded, checking if profile id exists");
      return this.checkAuthAndRedirect(route, state);
    } else {
      this.loader$.next(true);
      console.log("Auth not loaded, waiting for authChanged");
      return new Observable<boolean>((observer) => {
        this.authService.authChanged.subscribe({
          next: () => {
            this.loader$.next(false);
            observer.next(this.checkAuthAndRedirect(route, state));
          }
        })
      });
    }
  }
}