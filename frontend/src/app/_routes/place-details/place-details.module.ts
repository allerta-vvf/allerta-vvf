import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { LeafletModule } from '@asymmetrik/ngx-leaflet';
import { BackBtnModule } from '../../_components/back-btn/back-btn.module';

import { PlaceDetailsRoutingModule } from './place-details-routing.module';
import { PlaceDetailsComponent } from './place-details.component';

@NgModule({
  declarations: [
    PlaceDetailsComponent
  ],
  imports: [
    CommonModule,
    PlaceDetailsRoutingModule,
    LeafletModule,
    BackBtnModule
  ]
})
export class PlaceDetailsModule { }
