import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { FormsModule } from '@angular/forms';
import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { TranslationModule } from '../../translation.module';

import { DatetimePickerComponent } from './datetime-picker.component';

@NgModule({
  declarations: [
    DatetimePickerComponent
  ],
  imports: [
    CommonModule,
    FormsModule,
    BsDatepickerModule.forRoot(),
    TranslationModule
  ],
  exports: [
    DatetimePickerComponent
  ]
})
export class DatetimePickerModule { }
