import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { MapPickerModule } from '../../_components/map-picker/map-picker.module';
import { PlacePickerModule } from 'src/app/_components/place-picker/place-picker.module';
import { DatetimePickerModule } from '../../_components/datetime-picker/datetime-picker.module';
import { BackBtnModule } from '../../_components/back-btn/back-btn.module';
import { TranslationModule } from '../../translation.module';
import { FirstLetterUppercasePipe } from '../../_pipes/first-letter-uppercase.pipe';

import { EditServiceRoutingModule } from './edit-service-routing.module';
import { EditServiceComponent } from './edit-service.component';

@NgModule({
  declarations: [
    EditServiceComponent
  ],
  imports: [
    CommonModule,
    EditServiceRoutingModule,
    FormsModule,
    ReactiveFormsModule,
    BsDatepickerModule.forRoot(),
    MapPickerModule,
    PlacePickerModule,
    DatetimePickerModule,
    BackBtnModule,
    TranslationModule,
    FirstLetterUppercasePipe
  ]
})
export class EditServiceModule { }
