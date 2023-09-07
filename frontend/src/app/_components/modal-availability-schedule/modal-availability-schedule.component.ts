import { Component, OnInit, ViewEncapsulation, HostListener } from '@angular/core';
import { BsModalRef } from 'ngx-bootstrap/modal';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { ToastrService } from 'ngx-toastr';
import { TranslateService } from '@ngx-translate/core';

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
      name: 'monday',
      short: 'monday_short'
    },
    {
      name: 'tuesday',
      short: 'tuesday_short'
    },
    {
      name: 'wednesday',
      short: 'wednesday_short'
    },
    {
      name: 'thursday',
      short: 'thursday_short'
    },
    {
      name: 'friday',
      short: 'friday_short'
    },
    {
      name: 'saturday',
      short: 'saturday_short'
    },
    {
      name: 'sunday',
      short: 'sunday_short'
    }
  ];
  public slots = Array(48).fill(0).map((x,i)=>i);

  public selectedCells: any = [];

  //Used for "select all"
  public selectedSlots: number[] = [];
  public selectedDays: number[] = [];

  public isSelecting = false;

  constructor(
    private toastr: ToastrService,
    public bsModalRef: BsModalRef,
    private api: ApiClientService,
    private translate: TranslateService
  ) { }

  loadSchedules(schedules: any) {
    console.log("Loaded schedules", schedules);
    if(typeof schedules === "undefined") {
      schedules = [];
    }
    if(typeof schedules === "string") {
      schedules = JSON.parse(schedules);
    }
    this.selectedCells = schedules;
  }

  ngOnInit(): void {
    this.orientation = window.innerHeight > window.innerWidth ? "portrait" : "landscape";
    this.api.get("schedules").then((response: any) => {
      this.loadSchedules(response);
    }).catch((err) => {
      if(err.status === 500) throw err;
      this.translate.get('list.schedule_load_failed').subscribe((res: string) => {
        this.toastr.error(res);
      });
    });
  }

  saveChanges() {
    console.log("Selected cells", this.selectedCells);
    this.api.post("schedules", {
      schedules: this.selectedCells
    }).catch((err) => {
      if(err.status === 500) throw err;
      this.translate.get('list.schedule_update_failed').subscribe((res: string) => {
        this.toastr.error(res);
      });
    });
    this.bsModalRef.hide();
  }

  @HostListener('window:resize', ['$event'])
  onResize(event: Event) {
    this.orientation = window.innerHeight > window.innerWidth ? "portrait" : "landscape";
  }

  isCellSelected(day: number, slot: number) {
    return this.selectedCells.find((cell: any) => cell.day === day && cell.slot === slot);
  }

  toggleCell(day: number, slot: number) {
    if(!this.isCellSelected(day, slot)) {
      this.selectedCells.push({
        day, slot
      });
    } else {
      this.selectedCells = this.selectedCells.filter((cell: any) => cell.day !== day || cell.slot !== slot);
    }
  }

  selectEverySlotWithHour(slot: number) {
    console.log("Slot hour selected", slot);
    debugger;
    if(this.selectedSlots.includes(slot)) {
      this.days.forEach((day: any, i: number) => {
        this.selectedCells = this.selectedCells.filter((cell: any) => cell.day !== i || cell.slot !== slot);
      });
      this.selectedSlots = this.selectedSlots.filter((h: number) => h !== slot);
    } else {
      this.days.forEach((day: any, i: number) => {
        if(!this.isCellSelected(i, slot)) {
          this.selectedCells.push({
            day: i, slot
          });
        }
      });
      this.selectedSlots.push(slot);
    }
  }

  selectEntireDay(day: number) {
    console.log("Day selected", day);
    if(this.selectedDays.includes(day)) {
      for (let slot = 0; slot < 48; slot++) {
        this.selectedCells = this.selectedCells.filter((cell: any) => cell.day !== day || cell.slot !== slot);
      }
      this.selectedDays = this.selectedDays.filter((i: number) => i !== day);
    } else {
      for (let slot = 0; slot < 48; slot++) {
        if(!this.isCellSelected(day, slot)) {
          this.selectedCells.push({
            day, slot
          });
        }
      }
      this.selectedDays.push(day);
    }
  }

  mouseDownCell(day: number, slot: number) {
    this.isSelecting = true;
    console.log("Mouse down");
    console.log("Slot cell selected", day, slot);
    this.toggleCell(day, slot);
    return false;
  }

  mouseUpCell() {
    this.isSelecting = false;
    console.log("Mouse up");
  }

  mouseOverCell(day: number, slot: number) {
    if (this.isSelecting) {
      console.log("Mouse over", day, slot);
      console.log("Slot cell selected", day, slot);
      this.toggleCell(day, slot);
    }
  }

}
