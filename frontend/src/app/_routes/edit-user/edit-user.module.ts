import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { BsDatepickerModule } from 'ngx-bootstrap/datepicker';
import { BackBtnModule } from '../../_components/back-btn/back-btn.module';
import { ModalAddMedicalExaminationModule } from 'src/app/_components/modal-add-medical-examination/modal-add-medical-examination.module';
import { TranslationModule } from '../../translation.module';

import { EditUserRoutingModule } from './edit-user-routing.module';
import { EditUserComponent } from './edit-user.component';

@NgModule({
  declarations: [
    EditUserComponent
  ],
  imports: [
    CommonModule,
    EditUserRoutingModule,
    FormsModule,
    ReactiveFormsModule,
    BsDatepickerModule.forRoot(),
    BackBtnModule,
    ModalAddMedicalExaminationModule,
    TranslationModule
  ]
})
export class EditUserModule { }
