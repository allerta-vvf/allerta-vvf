import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { ListComponent } from './_components/list/list.component';
import { LogsComponent } from './_components/logs/logs.component';
import { ServicesComponent } from './_components/services/services.component';
import { TrainingsComponent } from './_components/trainings/trainings.component';

const routes: Routes = [
  { path: 'list', component: ListComponent },
  { path: 'logs', component: LogsComponent },
  { path: 'services', component: ServicesComponent },
  { path: 'trainings', component: TrainingsComponent },
  //
  { path: "**", redirectTo: "/list", pathMatch: "full" },
];

@NgModule({
  imports: [RouterModule.forRoot(routes, { useHash: true })],
  exports: [RouterModule]
})
export class AppRoutingModule { }
