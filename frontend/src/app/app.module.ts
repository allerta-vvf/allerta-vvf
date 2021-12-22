import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { HttpClientModule, HttpClient } from '@angular/common/http';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { ServiceWorkerModule } from '@angular/service-worker';
import { environment } from '../environments/environment';

import { TableComponent } from './table/table.component';
import { OwnerImageComponent } from './_components/owner-image/owner-image.component';

import { ListComponent } from './_components/list/list.component';
import { LogsComponent } from './_components/logs/logs.component';
import { ServicesComponent } from './_components/services/services.component';
import { TrainingsComponent } from './_components/trainings/trainings.component';

@NgModule({
  declarations: [
    AppComponent,
    TableComponent,
    OwnerImageComponent,
    //
    ListComponent,
    LogsComponent,
    ServicesComponent,
    TrainingsComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    HttpClientModule,
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
