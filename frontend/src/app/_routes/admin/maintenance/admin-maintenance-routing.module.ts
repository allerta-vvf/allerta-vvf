import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AdminMaintenanceComponent } from './admin-maintenance.component';

const routes: Routes = [{ path: '', component: AdminMaintenanceComponent }];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminMaintenanceRoutingModule { }
