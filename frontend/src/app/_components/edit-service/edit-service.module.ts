import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { MapPickerModule } from '../map-picker/map-picker.module';

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
    MapPickerModule
  ]
})
export class EditServiceModule { }
