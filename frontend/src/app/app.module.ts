import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { HttpClient, HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { TranslateLoader, TranslateModule } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';
import { TranslationModule } from './translation.module';
import { ToastrModule } from 'ngx-toastr';
import { ModalModule } from 'ngx-bootstrap/modal';
import { TooltipModule } from 'ngx-bootstrap/tooltip';
import { CollapseModule } from 'ngx-bootstrap/collapse';
import { AlertModule } from 'ngx-bootstrap/alert';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { ServiceWorkerModule } from '@angular/service-worker';
import { environment } from '../environments/environment';

import { TableComponent } from './_components/table/table.component';
import { ModalAvailabilityScheduleComponent } from './_components/modal-availability-schedule/modal-availability-schedule.component';
import { ModalAlertComponent } from './_components/modal-alert/modal-alert.component';
import { OwnerImageComponent } from './_components/owner-image/owner-image.component';

import { LoginComponent } from './_routes/login/login.component';

import { ListComponent } from './_routes/list/list.component';
import { LogsComponent } from './_routes/logs/logs.component';
import { ServicesComponent } from './_routes/services/services.component';
import { TrainingsComponent } from './_routes/trainings/trainings.component';

import { AuthInterceptor } from './_providers/auth-interceptor.provider';

@NgModule({
  declarations: [
    AppComponent,
    //
    TableComponent,
    ModalAvailabilityScheduleComponent,
    ModalAlertComponent,
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
    CollapseModule.forRoot(),
    AlertModule.forRoot(),
    ServiceWorkerModule.register('ngsw-worker.js', {
      enabled: false && environment.production,
      // Register the ServiceWorker as soon as the app is stable
      // or after 30 seconds (whichever comes first).
      registrationStrategy: 'registerWhenStable:30000'
    }),
    TranslateModule.forRoot({
      loader: {
        provide: TranslateLoader,
        useFactory: HttpLoaderFactory,
        deps: [HttpClient]
      }
    }),
    TranslationModule
  ],
  providers: [{
    provide: HTTP_INTERCEPTORS, 
    useClass: AuthInterceptor,
    multi: true
  }],
  bootstrap: [AppComponent]
})
export class AppModule { }

export function HttpLoaderFactory(http: HttpClient): TranslateHttpLoader {
  return new TranslateHttpLoader(http, './assets/i18n/', '.json');
}