import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'ftitlecase',
  standalone: true
})
export class FirstLetterUppercasePipe implements PipeTransform {

  transform(value: unknown, ...args: unknown[]): unknown {
    if(typeof value !== "string") return value;
    return value.charAt(0).toUpperCase() + value.slice(1);
  }

}
