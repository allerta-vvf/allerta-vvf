import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { FormsModule } from '@angular/forms';
import { LeafletModule } from '@asymmetrik/ngx-leaflet';
import { TranslationModule } from '../../translation.module';

import { MapPickerComponent } from './map-picker.component';

@NgModule({
  declarations: [
    MapPickerComponent
  ],
  imports: [
    CommonModule,
    FormsModule,
    LeafletModule,
    TranslationModule
  ],
  exports: [
    MapPickerComponent
  ]
})
export class MapPickerModule { }
