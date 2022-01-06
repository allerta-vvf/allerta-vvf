import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { ListComponent } from './_components/list/list.component';
import { LogsComponent } from './_components/logs/logs.component';
import { ServicesComponent } from './_components/services/services.component';
import { EditServiceComponent } from './_components/edit-service/edit-service.component';
import { TrainingsComponent } from './_components/trainings/trainings.component';

import { AuthorizeGuard } from './_guards/authorize.guard';
import { LoginComponent } from './_components/login/login.component';

const routes: Routes = [
  { path: 'list', component: ListComponent, canActivate: [AuthorizeGuard] },
  { path: 'logs', component: LogsComponent, canActivate: [AuthorizeGuard] },
  { path: 'services', component: ServicesComponent, canActivate: [AuthorizeGuard] },
  { path: 'services/:id', component: EditServiceComponent, canActivate: [AuthorizeGuard] },
  { path: 'trainings', component: TrainingsComponent, canActivate: [AuthorizeGuard] },
  { path: "login/:redirect/:extraParam", component: LoginComponent },
  { path: "login/:redirect", component: LoginComponent },
  //
  { path: "**", redirectTo: "/list", pathMatch: "full" },
];

@NgModule({
  imports: [RouterModule.forRoot(routes, { useHash: true })],
  exports: [RouterModule]
})
export class AppRoutingModule { }
