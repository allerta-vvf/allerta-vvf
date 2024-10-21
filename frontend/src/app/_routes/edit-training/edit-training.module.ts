import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { MapPickerModule } from '../../_components/map-picker/map-picker.module';
import { DatetimePickerModule } from '../../_components/datetime-picker/datetime-picker.module';
import { BackBtnModule } from '../../_components/back-btn/back-btn.module';
import { TranslationModule } from '../../translation.module';
import { FirstLetterUppercasePipe } from '../../_pipes/first-letter-uppercase.pipe';

import { EditTrainingRoutingModule } from './edit-training-routing.module';
import { EditTrainingComponent } from './edit-training.component';

@NgModule({
  declarations: [
    EditTrainingComponent
  ],
  imports: [
    CommonModule,
    EditTrainingRoutingModule,
    FormsModule,
    ReactiveFormsModule,
    BsDatepickerModule.forRoot(),
    MapPickerModule,
    DatetimePickerModule,
    BackBtnModule,
    TranslationModule,
    FirstLetterUppercasePipe
  ]
})
export class EditTrainingModule { }
