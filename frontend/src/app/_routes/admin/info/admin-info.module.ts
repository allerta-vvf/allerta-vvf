import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { TranslationModule } from '../../../translation.module';
import { FirstLetterUppercasePipe } from '../../../_pipes/first-letter-uppercase.pipe';

import { AdminInfoComponent } from './admin-info.component';
import { AdminInfoRoutingModule } from './admin-info-routing.module';

@NgModule({
  declarations: [
    AdminInfoComponent
  ],
  imports: [
    CommonModule,
    AdminInfoRoutingModule,
    TranslationModule,
    FirstLetterUppercasePipe
  ]
})
export class AdminInfoModule { }
