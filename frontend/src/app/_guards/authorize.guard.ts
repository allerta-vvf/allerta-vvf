import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, Router, RouterStateSnapshot, UrlTree } from '@angular/router';
import { Observable } from 'rxjs';
import { AuthService } from '../_services/auth.service';

@Injectable({
  providedIn: 'root'
})
export class AuthorizeGuard implements CanActivate {
  constructor(private authService: AuthService, private router: Router) {
  }

  checkAuthAndRedirect(
    route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ): boolean {
    console.log(this.authService, route, state);
    if(this.authService.profile === undefined) {
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
      return this.checkAuthAndRedirect(route, state);
    } else {
      return new Observable<boolean>((observer) => {
        this.authService.authChanged.subscribe({
          next: () => { observer.next(this.checkAuthAndRedirect(route, state)); }
        })
      });
    }
  }
}