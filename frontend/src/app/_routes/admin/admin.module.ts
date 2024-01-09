import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { TabsModule } from 'ngx-bootstrap/tabs';
import { TranslationModule } from '../../translation.module';
import { FirstLetterUppercasePipe } from '../../_pipes/first-letter-uppercase.pipe';

import { AdminComponent } from './admin.component';
import { AdminRoutingModule } from './admin-routing.module';

@NgModule({
  declarations: [
    AdminComponent
  ],
  imports: [
    CommonModule,
    AdminRoutingModule,
    TabsModule.forRoot(),
    TranslationModule,
    FirstLetterUppercasePipe
  ]
})
export class AdminModule { }
