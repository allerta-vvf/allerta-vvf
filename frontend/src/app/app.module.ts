import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { HttpClientModule } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { ToastrModule } from 'ngx-toastr';
import { ModalModule } from 'ngx-bootstrap/modal';
import { TooltipModule } from 'ngx-bootstrap/tooltip';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { ServiceWorkerModule } from '@angular/service-worker';
import { environment } from '../environments/environment';

import { TableComponent } from './_components/table/table.component';
import { ModalAvailabilityScheduleComponent } from './_components/modal-availability-schedule/modal-availability-schedule.component';
import { OwnerImageComponent } from './_components/owner-image/owner-image.component';

import { LoginComponent } from './_components/login/login.component';

import { ListComponent } from './_components/list/list.component';
import { LogsComponent } from './_components/logs/logs.component';
import { ServicesComponent } from './_components/services/services.component';
import { TrainingsComponent } from './_components/trainings/trainings.component';

@NgModule({
  declarations: [
    AppComponent,
    //
    TableComponent,
    ModalAvailabilityScheduleComponent,
    OwnerImageComponent,
    //
    LoginComponent,
    //
    ListComponent,
    LogsComponent,
    ServicesComponent,
    TrainingsComponent
  ],
  imports: [
    BrowserModule,
    BrowserAnimationsModule,
    AppRoutingModule,
    HttpClientModule,
    FormsModule,
    ToastrModule.forRoot({
      progressBar: true,
      easeTime: 300,
      timeOut: 2500,
      positionClass: 'toast-bottom-right'
    }),
    ModalModule.forRoot(),
    TooltipModule.forRoot(),
    ServiceWorkerModule.register('ngsw-worker.js', {
      enabled: false && environment.production,
      // Register the ServiceWorker as soon as the app is stable
      // or after 30 seconds (whichever comes first).
      registrationStrategy: 'registerWhenStable:30000'
    })
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
