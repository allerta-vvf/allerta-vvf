import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { FormsModule } from '@angular/forms';
import { LeafletModule } from '@asymmetrik/ngx-leaflet';
import { TranslationModule } from '../../translation.module';

import { MapComponent } from './map.component';

@NgModule({
  declarations: [
    MapComponent
  ],
  imports: [
    CommonModule,
    FormsModule,
    LeafletModule,
    TranslationModule
  ],
  exports: [
    MapComponent
  ]
})
export class MapModule { }
