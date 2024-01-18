import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { TranslationModule } from '../../../translation.module';
import { FirstLetterUppercasePipe } from '../../../_pipes/first-letter-uppercase.pipe';

import { AdminOptionsComponent } from './admin-options.component';
import { AdminOptionsRoutingModule } from './admin-options-routing.module';

@NgModule({
  declarations: [
    AdminOptionsComponent
  ],
  imports: [
    CommonModule,
    FormsModule,
    AdminOptionsRoutingModule,
    TranslationModule,
    FirstLetterUppercasePipe
  ]
})
export class AdminOptionsModule { }
