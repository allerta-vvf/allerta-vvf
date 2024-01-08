import { NgModule, ErrorHandler, APP_INITIALIZER } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { HttpClient, HttpClientModule, HttpClientXsrfModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { TranslateLoader, TranslateModule } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';
import { TranslationModule } from './translation.module';
import { Router } from "@angular/router";
import * as Sentry from "@sentry/angular-ivy";
import { ToastrModule } from 'ngx-toastr';
import { ModalModule } from 'ngx-bootstrap/modal';
import { TooltipModule } from 'ngx-bootstrap/tooltip';
import { CollapseModule } from 'ngx-bootstrap/collapse';
import { AlertModule } from 'ngx-bootstrap/alert';
import { PaginationModule } from 'ngx-bootstrap/pagination';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { ServiceWorkerModule } from '@angular/service-worker';
import { environment } from '../environments/environment';

import { TableComponent } from './_components/table/table.component';
import { ModalAvailabilityScheduleComponent } from './_components/modal-availability-schedule/modal-availability-schedule.component';
import { ModalAlertComponent } from './_components/modal-alert/modal-alert.component';
import { ModalUserInfoComponent } from './_components/modal-user-info/modal-user-info.component';
import { OwnerImageComponent } from './_components/owner-image/owner-image.component';

import { DaterangePickerModule } from './_components/daterange-picker/daterange-picker.module';

import { LoginComponent } from './_routes/login/login.component';

import { ListComponent } from './_routes/list/list.component';
import { LogsComponent } from './_routes/logs/logs.component';
import { ServicesComponent } from './_routes/services/services.component';
import { TrainingsComponent } from './_routes/trainings/trainings.component';

import { AuthInterceptor } from './_providers/auth-interceptor.provider';

//import { ApplicationPipesModule } from './_pipes/application-pipes.module';
import { FirstLetterUppercasePipe } from './_pipes/first-letter-uppercase.pipe';

@NgModule({
  declarations: [
    AppComponent,
    //
    TableComponent,
    ModalAvailabilityScheduleComponent,
    ModalAlertComponent,
    ModalUserInfoComponent,
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
    HttpClientXsrfModule.disable(), //See auth-interceptor.provider.ts
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
    DaterangePickerModule,
    PaginationModule.forRoot(),
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
    TranslationModule,
    //ApplicationPipesModule
    FirstLetterUppercasePipe
  ],
  exports: [
    FirstLetterUppercasePipe
  ],
  providers: [
    {
      provide: HTTP_INTERCEPTORS,
      useClass: AuthInterceptor,
      multi: true
    },
    {
      provide: ErrorHandler,
      useValue: Sentry.createErrorHandler({
        showDialog: true,
        dialogOptions: {
          title: "E' stato rilevato un errore",
          subtitle: "Gli sviluppatori sono stati avvisati e stanno lavorando per risolvere il problema.",
          subtitle2: "Compilare questo modulo può aiutarci a risolvere il problema più velocemente.",
          labelName: "Nome",
          labelComments: "Cosa stavi facendo quando si è verificato l'errore?",
          labelClose: "Chiudi",
          labelSubmit: "Invia",
          errorGeneric: "Si è verificato un errore",
          errorFormEntry: "Compilare tutti i campi obbligatori",
          successMessage: "Grazie per il tuo aiuto!"
        }
      }),
    },
    {
      provide: Sentry.TraceService,
      deps: [Router],
    },
    {
      provide: APP_INITIALIZER,
      useFactory: () => () => {},
      deps: [Sentry.TraceService],
      multi: true,
    },
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }

export function HttpLoaderFactory(http: HttpClient): TranslateHttpLoader {
  return new TranslateHttpLoader(http, './assets/i18n/', '.json');
}