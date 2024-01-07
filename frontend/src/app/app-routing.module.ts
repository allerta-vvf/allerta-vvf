import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { ListComponent } from './_routes/list/list.component';
import { LogsComponent } from './_routes/logs/logs.component';
import { ServicesComponent } from './_routes/services/services.component';
import { TrainingsComponent } from './_routes/trainings/trainings.component';

import { AuthorizeGuard } from './_guards/authorize.guard';
import { LoginComponent } from './_routes/login/login.component';

const routes: Routes = [
  { path: 'list', component: ListComponent, canActivate: [AuthorizeGuard] },
  {
    path: 'users/:id', 
    loadChildren: () => import('./_routes/edit-user/edit-user.module').then(m => m.EditUserModule),
    canActivate: [AuthorizeGuard]
  },
  { path: 'logs', component: LogsComponent, canActivate: [AuthorizeGuard] },
  { path: 'services', component: ServicesComponent, canActivate: [AuthorizeGuard], data: {permissionsRequired: ['services-read']} },
  {
    path: 'place-details', 
    loadChildren: () => import('./_routes/place-details/place-details.module').then(m => m.PlaceDetailsModule),
    canActivate: [AuthorizeGuard]
  },
  {
    path: 'services/:id', 
    loadChildren: () => import('./_routes/edit-service/edit-service.module').then(m => m.EditServiceModule),
    canActivate: [AuthorizeGuard],
    data: {permissionsRequired: ['services-read', 'services-update']}
  },
  { path: 'trainings', component: TrainingsComponent, canActivate: [AuthorizeGuard], data: {permissionsRequired: ['trainings-read']} },
  {
    path: 'trainings/:id', 
    loadChildren: () => import('./_routes/edit-training/edit-training.module').then(m => m.EditTrainingModule),
    canActivate: [AuthorizeGuard],
    data: {permissionsRequired: ['trainings-read', 'trainings-update']}
  },
  {
    path: 'stats', 
    loadChildren: () => import('./_routes/stats/stats.module').then(m => m.StatsModule),
    canActivate: [AuthorizeGuard]
  },
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
