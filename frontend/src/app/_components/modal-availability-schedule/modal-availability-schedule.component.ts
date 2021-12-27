import { Component, OnInit, ViewEncapsulation, HostListener } from '@angular/core';
import { BsModalRef } from 'ngx-bootstrap/modal';

@Component({
  selector: 'modal-availability-schedule',
  templateUrl: './modal-availability-schedule.component.html',
  styleUrls: ['./modal-availability-schedule.component.scss'],
  encapsulation: ViewEncapsulation.None
})
export class ModalAvailabilityScheduleComponent implements OnInit {
  public orientation = "portrait";

  public days = [
    {
      name: 'Lunedì',
      short: 'Lun'
    },
    {
      name: 'Martedì',
      short: 'Mar'
    },
    {
      name: 'Mercoledì',
      short: 'Mer'
    },
    {
      name: 'Giovedì',
      short: 'Gio'
    },
    {
      name: 'Venerdì',
      short: 'Ven'
    },
    {
      name: 'Sabato',
      short: 'Sab'
    },
    {
      name: 'Domenica',
      short: 'Dom'
    }
  ];
  public hours = [
    "0:00", "0:30",
    "1:00", "1:30",
    "2:00", "2:30",
    "3:00", "3:30",
    "4:00", "4:30",
    "5:00", "5:30",
    "6:00", "6:30",
    "7:00", "7:30",
    "8:00", "8:30",
    "9:00", "9:30",
    "10:00", "10:30",
    "11:00", "11:30",
    "12:00", "12:30",
    "13:00", "13:30",
    "14:00", "14:30",
    "15:00", "15:30",
    "16:00", "16:30",
    "17:00", "17:30",
    "18:00", "18:30",
    "19:00", "19:30",
    "20:00", "20:30",
    "21:00", "21:30",
    "22:00", "22:30",
    "23:00", "23:30",
  ];

  public selectedCells: any = [];

  //Used for "select all"
  public selectedHours: string[] = [];
  public selectedDays: number[] = [];

  public isSelecting = false;

  constructor(public bsModalRef: BsModalRef) { }

  ngOnInit(): void {
    this.orientation = window.innerHeight > window.innerWidth ? "portrait" : "landscape";
  }

  @HostListener('window:resize', ['$event'])
  onResize(event: Event) {
    this.orientation = window.innerHeight > window.innerWidth ? "portrait" : "landscape";
  }

  isCellSelected(day: number, hour: string) {
    return this.selectedCells.find((cell: any) => cell.day === day && cell.hour === hour);
  }

  toggleCell(day: number, hour: string) {
    if(!this.isCellSelected(day, hour)) {
      this.selectedCells.push({
        day, hour
      });
    } else {
      this.selectedCells = this.selectedCells.filter((cell: any) => cell.day !== day || cell.hour !== hour);
    }
  }

  selectHour(hour: string) {
    console.log("Hour selected", hour);
    if(this.selectedHours.includes(hour)) {
      this.days.forEach((day: any, i: number) => {
        this.selectedCells = this.selectedCells.filter((cell: any) => cell.day !== i || cell.hour !== hour);
      });
      this.selectedHours = this.selectedHours.filter((h: string) => h !== hour);
    } else {
      this.days.forEach((day: any, i: number) => {
        if(!this.isCellSelected(i, hour)) {
          this.selectedCells.push({
            day: i, hour
          });
        }
      });
      this.selectedHours.push(hour);
    }
  }

  selectDay(day: number) {
    console.log("Day selected", day);
    if(this.selectedDays.includes(day)) {
      this.hours.forEach((hour: string) => {
        this.selectedCells = this.selectedCells.filter((cell: any) => cell.day !== day || cell.hour !== hour);
      });
      this.selectedDays = this.selectedDays.filter((i: number) => i !== day);
    } else {
      this.hours.forEach((hour: string) => {
        if(!this.isCellSelected(day, hour)) {
          this.selectedCells.push({
            day, hour
          });
        }
      });
      this.selectedDays.push(day);
    }
  }

  mouseDownCell(day: number, hour: string) {
    this.isSelecting = true;
    console.log("Mouse down");
    console.log("Hour cell selected", day, hour);
    this.toggleCell(day, hour);
    return false;
  }

  mouseUpCell() {
    this.isSelecting = false;
    console.log("Mouse up");
  }

  mouseOverCell(day: number, hour: string) {
    if (this.isSelecting) {
      console.log("Mouse over", day, hour);
      console.log("Hour cell selected", day, hour);
      this.toggleCell(day, hour);
    }
  }

}
