import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslateModule, TranslateService } from '@ngx-translate/core';

@NgModule({
  exports: [
    CommonModule,
    TranslateModule
  ]
})
export class TranslationModule {
  constructor(private translate: TranslateService) {
    this.translate.setDefaultLang('en');
    this.translate.use(window.navigator.language.split("-")[0]);
  }
}
