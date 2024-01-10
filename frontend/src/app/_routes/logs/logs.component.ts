import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-logs',
  templateUrl: './logs.component.html',
  styleUrls: ['./logs.component.scss']
})
export class LogsComponent implements OnInit {
  initialStartFilter = new Date(new Date().setDate(new Date().getDate() - 30));

  constructor() { }

  ngOnInit(): void {
  }

}
