import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AdminInfoComponent } from './admin-info.component';

const routes: Routes = [{ path: '', component: AdminInfoComponent }];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminInfoRoutingModule { }
