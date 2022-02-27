import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslationModule } from '../../translation.module';

import { BackBtnComponent } from './back-btn.component';

@NgModule({
  declarations: [
    BackBtnComponent
  ],
  imports: [
    CommonModule,
    TranslationModule
  ],
  exports: [
    BackBtnComponent
  ]
})
export class BackBtnModule { }
