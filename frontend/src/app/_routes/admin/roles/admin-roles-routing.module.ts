import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AdminRolesComponent } from './admin-roles.component';

const routes: Routes = [{ path: '', component: AdminRolesComponent }];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminRolesRoutingModule { }
