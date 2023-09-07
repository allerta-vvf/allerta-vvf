import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'app-trainings',
  templateUrl: './trainings.component.html',
  styleUrls: ['./trainings.component.scss']
})
export class TrainingsComponent implements OnInit {

  constructor(private router: Router) { }

  ngOnInit(): void {
  }

  addTraining() {
    this.router.navigate(['trainings', 'new']);
  }

}
