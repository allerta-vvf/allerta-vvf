import { Component, OnInit, forwardRef } from '@angular/core';
import { NG_VALUE_ACCESSOR, ControlValueAccessor } from '@angular/forms';
import { defineLocale } from 'ngx-bootstrap/chronos';
import { BsLocaleService } from 'ngx-bootstrap/datepicker';
import { itLocale } from 'ngx-bootstrap/locale';
defineLocale('it', itLocale);

@Component({
  selector: 'daterange-picker',
  templateUrl: './daterange-picker.component.html',
  styleUrls: ['./daterange-picker.component.scss'],
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      multi: true,
      useExisting: forwardRef(() => DaterangePickerComponent)
    }
  ]
})
export class DaterangePickerComponent implements OnInit, ControlValueAccessor {
  disabled = false;

  maxDate: Date = new Date();

  dateRangePickerOptions = {
    ranges: [
      {
        value: [new Date(new Date().setDate(new Date().getDate() - 31)), new Date()],
        label: 'Ultimi 31 giorni'
      }, {
        value: [new Date(new Date().setDate(new Date().getDate() - 7)), new Date()],
        label: 'Ultimi 7 giorni'
      }, {
        value: [new Date(new Date().getFullYear(), 0, 1), new Date()],
        label: 'Anno corrente'
      }, {
        value: [new Date(new Date().getFullYear() - 1, 0, 1), new Date(new Date().getFullYear() - 1, 11, 31)],
        label: 'Anno precedente'
      }, {
        value: [new Date(new Date().setMonth(new Date().getMonth() - 6)), new Date()],
        label: 'Ultimi 6 mesi'
      }
    ]
  };

  range: (Date | undefined)[] | undefined = undefined;

  constructor(private localeService: BsLocaleService) {
  }

  ngOnInit(): void {
    this.localeService.use(window.navigator.language.split("-")[0]);
  }

  get value(): (Date | undefined)[] | undefined {
    if(this.range === null) return undefined;
    return this.range;
  }

  set value(range: (Date | undefined)[] | undefined) {
    console.log("new value", range, "old value", this.range);
    this.range = range;
    this.onChange(this.range);
  }

  setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
  }

  updateValue($event: (Date | undefined)[] | undefined) {
    this.range = $event;
    console.log("updateValue", this.range);    
    this.markAsTouched();
    this.onChange($event);
  }

  resetRange() {
    this.updateValue(undefined);
  }

  writeValue(range: (Date | undefined)[] | undefined): void {
    this.range = range;
  }

  onChange = (value: (Date | undefined)[] | undefined) => {};

  onTouched = () => {};

  registerOnChange(fn: (value: (Date | undefined)[] | undefined) => void): void {
    this.onChange = fn;
    console.log(fn);
  }

  registerOnTouched(fn: () => void): void {
    this.onTouched = fn;
    fn();
  }

  markAsTouched() {
    this.onTouched();
  }
}
