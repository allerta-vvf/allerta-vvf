import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { CollapseModule } from 'ngx-bootstrap/collapse';
import { TranslationModule } from '../../../translation.module';
import { FirstLetterUppercasePipe } from '../../../_pipes/first-letter-uppercase.pipe';

import { AdminMaintenanceComponent } from './admin-maintenance.component';
import { AdminMaintenanceRoutingModule } from './admin-maintenance-routing.module';

@NgModule({
  declarations: [
    AdminMaintenanceComponent
  ],
  imports: [
    CommonModule,
    AdminMaintenanceRoutingModule,
    CollapseModule.forRoot(),
    TranslationModule,
    FirstLetterUppercasePipe
  ]
})
export class AdminMaintenanceModule { }
