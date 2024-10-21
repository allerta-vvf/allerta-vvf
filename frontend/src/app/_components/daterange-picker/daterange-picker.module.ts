import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { FormsModule } from '@angular/forms';
import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { TranslationModule } from '../../translation.module';

import { DaterangePickerComponent } from './daterange-picker.component';

@NgModule({
  declarations: [
    DaterangePickerComponent
  ],
  imports: [
    CommonModule,
    FormsModule,
    BsDatepickerModule.forRoot(),
    TranslationModule
  ],
  exports: [
    DaterangePickerComponent
  ]
})
export class DaterangePickerModule { }
