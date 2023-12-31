import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { StatsServicesComponent } from './stats-services/stats-services.component';

const routes: Routes = [
  { path: 'services', component: StatsServicesComponent }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class StatsRoutingModule { }
