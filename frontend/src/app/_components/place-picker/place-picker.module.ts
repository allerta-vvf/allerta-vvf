import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { FormsModule } from '@angular/forms';
import { TypeaheadModule } from 'ngx-bootstrap/typeahead';
import { TranslationModule } from '../../translation.module';

import { PlacePickerComponent } from './place-picker.component';

@NgModule({
  declarations: [
    PlacePickerComponent
  ],
  imports: [
    CommonModule,
    FormsModule,
    TypeaheadModule,
    TranslationModule
  ],
  exports: [
    PlacePickerComponent
  ]
})
export class PlacePickerModule { }
