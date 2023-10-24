import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { EditTrainingComponent } from './edit-training.component';

const routes: Routes = [{ path: '', component: EditTrainingComponent }];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class EditTrainingRoutingModule { }
