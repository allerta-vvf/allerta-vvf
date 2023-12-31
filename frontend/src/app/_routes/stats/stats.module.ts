import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { DatetimePickerModule } from '../../_components/datetime-picker/datetime-picker.module';
import { DaterangePickerModule } from '../../_components/daterange-picker/daterange-picker.module';
import { BackBtnModule } from '../../_components/back-btn/back-btn.module';
import { MapModule } from 'src/app/_components/map/map.module';
import { ChartModule } from '../../_components/chart/chart.module';
import { TranslationModule } from '../../translation.module';

import { StatsRoutingModule } from './stats-routing.module';
import { StatsServicesComponent } from './stats-services/stats-services.component';

@NgModule({
  declarations: [
    StatsServicesComponent
  ],
  imports: [
    CommonModule,
    StatsRoutingModule,
    FormsModule,
    ReactiveFormsModule,
    BsDatepickerModule.forRoot(),
    DatetimePickerModule,
    DaterangePickerModule,
    BackBtnModule,
    MapModule,
    ChartModule,
    TranslationModule
  ]
})
export class StatsModule { }
