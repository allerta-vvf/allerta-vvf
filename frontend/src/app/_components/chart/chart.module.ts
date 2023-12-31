import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { FormsModule } from '@angular/forms';
import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { TranslationModule } from '../../translation.module';
import { ButtonModule } from 'primeng/button'; 
import { ChartModule as OrigChartModule } from 'primeng/chart';

import { ChartComponent } from './chart.component';

@NgModule({
  declarations: [
    ChartComponent
  ],
  imports: [
    CommonModule,
    FormsModule,
    BsDatepickerModule.forRoot(),
    TranslationModule,
    ButtonModule,
    OrigChartModule
  ],
  exports: [
    ChartComponent
  ]
})
export class ChartModule { }
