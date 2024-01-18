import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AdminOptionsComponent } from './admin-options.component';

const routes: Routes = [{ path: '', component: AdminOptionsComponent }];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminOptionsRoutingModule { }
