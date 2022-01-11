import { Component, OnInit } from '@angular/core';
import { LocationBackService } from 'src/app/_services/locationBack.service';

@Component({
  selector: 'back-btn',
  templateUrl: './back-btn.component.html',
  styleUrls: ['./back-btn.component.scss']
})
export class BackBtnComponent implements OnInit {

  constructor(public locationBackService: LocationBackService) { }

  ngOnInit(): void {
  }

}
