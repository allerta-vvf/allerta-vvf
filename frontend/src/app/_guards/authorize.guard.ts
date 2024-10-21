import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, Router, RouterStateSnapshot, UrlTree } from '@angular/router';
import { Observable } from 'rxjs';
import { GuardLoaderIconService } from '../_services/guard-loader-icon.service';
import { AuthService } from '../_services/auth.service';
import { TranslateService } from '@ngx-translate/core';
import Swal from 'sweetalert2';

@Injectable({
  providedIn: 'root'
})
export class AuthorizeGuard  {
  constructor(
    private authService: AuthService,
    private guardLoaderIconService: GuardLoaderIconService,
    private router: Router,
    private translateService: TranslateService
  ) { }

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
      console.log(route.data);
      if(route.data["permissionsRequired"]) {
        let permissionsRequired = route.data["permissionsRequired"];
        console.log(permissionsRequired, this.authService.profile.permissions);
        if(!permissionsRequired.every((permission: string) => this.authService.profile.permissions.includes(permission))) {
          Swal.fire({
            title: this.translateService.instant("not_enough_permissions"),
            icon: "error",
            confirmButtonText: "Ok"
          });
          return false;
        }
      }
      return true;
    }
  }

  canActivate(
    route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot
  ): Observable<boolean | UrlTree> | Promise<boolean | UrlTree> | boolean | UrlTree {
    console.log(this.router.url, route, state);
    if(this.authService.authLoaded()) {
      this.guardLoaderIconService.hide();
      console.log("Auth already loaded, checking if profile id exists");
      return this.checkAuthAndRedirect(route, state);
    } else {
      this.guardLoaderIconService.show();
      console.log("Auth not loaded, waiting for authChanged");
      return new Observable<boolean>((observer) => {
        const proceed = () => {
          this.guardLoaderIconService.hide();
          observer.next(this.checkAuthAndRedirect(route, state));
        };
        this.authService.authChanged.subscribe({
          next: proceed
        });

        /*
        Fix for a race condition in admin page:
        1. Page loaded,
        2. Auth service loads profile, in the meantime admin page loads and checks authLoaded() which is false
        3. First router waits for authChanged, authChanged is emitted, second router is loaded
        4. authLoaded() still false, second router waits for authChanged but already emitted
        */
        setTimeout(() => {
          if(this.authService.authLoaded()) {
            proceed();
          }
        }, 200);
      });
    }
  }
}