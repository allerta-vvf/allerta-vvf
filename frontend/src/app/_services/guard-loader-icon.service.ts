import { Injectable } from '@angular/core'
import { Subject } from 'rxjs';
import { debounceTime } from 'rxjs/operators';

//Based on https://stackoverflow.com/a/48848557

@Injectable({ providedIn: 'root' })
export class GuardLoaderIconService {
  public loader$ = new Subject<boolean>();
  public loader = false;
  public locked = false;

  constructor() {
    this.loader$.pipe(
      debounceTime(250)
    ).subscribe((loader) => {
      this.loader = loader;
    });    
  }

  public show() {
    if(this.locked) return;
    this.loader$.next(true);
  }

  public hide() {
    if(this.locked) return;
    this.loader$.next(false);
  }

  public lock() {
    this.locked = true;
  }

  public unlock() {
    this.locked = false;
  }
}
