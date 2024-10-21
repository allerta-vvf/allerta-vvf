import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { TranslationModule } from '../../../translation.module';
import { FirstLetterUppercasePipe } from '../../../_pipes/first-letter-uppercase.pipe';

import { AdminRolesComponent } from './admin-roles.component';
import { AdminRolesRoutingModule } from './admin-roles-routing.module';

@NgModule({
  declarations: [
    AdminRolesComponent
  ],
  imports: [
    CommonModule,
    AdminRolesRoutingModule,
    TranslationModule,
    FirstLetterUppercasePipe
  ]
})
export class AdminRolesModule { }
