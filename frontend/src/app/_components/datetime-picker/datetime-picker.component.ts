import { Component, OnInit, forwardRef } from '@angular/core';
import { NG_VALUE_ACCESSOR, ControlValueAccessor } from '@angular/forms';
import { defineLocale } from 'ngx-bootstrap/chronos';
import { BsLocaleService } from 'ngx-bootstrap/datepicker';
import { itLocale } from 'ngx-bootstrap/locale';
defineLocale('it', itLocale);

@Component({
  selector: 'datetime-picker',
  templateUrl: './datetime-picker.component.html',
  styleUrls: ['./datetime-picker.component.scss'],
  providers: [
    {
      provide: NG_VALUE_ACCESSOR,
      multi: true,
      useExisting: forwardRef(() => DatetimePickerComponent)
    }
  ]
})
export class DatetimePickerComponent implements OnInit, ControlValueAccessor {
  disabled = false;

  date: Date = new Date();
  time: string = "";

  constructor(private localeService: BsLocaleService) {
  }

  ngOnInit(): void {
    this.localeService.use('it');
  }

  get value(): Date {
    let date = this.date;
    if(this.time) {
      date.setHours(parseInt(this.time.split(":")[0]));
      date.setMinutes(parseInt(this.time.split(":")[1]));
    }
    date.setSeconds(0, 0);
    return date;
  }

  set value(value: Date | number) {
    console.log("new value", value, "old value", this.value);
    if(!value || typeof value === "object") return;

    if(typeof value === "number") {
      this.date = new Date(value);
    } else {
      this.date = value;
    }
    this.time = this.date.getHours().toString().padStart(2, '0') + ":" + this.date.getMinutes().toString().padStart(2, '0');
    
    this.onChange(this.value);
  }

  setDisabledState(isDisabled: boolean): void {
    this.disabled = isDisabled;
  }

  updateValue() {
    this.markAsTouched();
    this.onChange(this.value);
  }

  writeValue(new_value: Date | number): void {
    this.value = new_value;
  }

  onChange = (value: Date) => {};

  onTouched = () => {};

  registerOnChange(fn: (value: Date) => void): void {
    this.onChange = fn;
  }

  registerOnTouched(fn: () => void): void {
    this.onTouched = fn;
    fn();
  }

  markAsTouched() {
    this.onTouched();
  }
}
