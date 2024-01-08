import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { TranslationModule } from '../../translation.module';
import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';

import { ModalAddMedicalExaminationComponent } from './modal-add-medical-examination.component';

@NgModule({
  declarations: [
    ModalAddMedicalExaminationComponent
  ],
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    TranslationModule,
    BsDatepickerModule.forRoot()
  ],
  exports: [
    ModalAddMedicalExaminationComponent
  ]
})
export class ModalAddMedicalExaminationModule { }
