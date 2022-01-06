import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { ApiClientService } from 'src/app/_services/api-client.service';

@Component({
  selector: 'app-edit-service',
  templateUrl: './edit-service.component.html',
  styleUrls: ['./edit-service.component.scss']
})
export class EditServiceComponent implements OnInit {
  public serviceId: string | undefined;
  public users: any[] = [];

  constructor(private route: ActivatedRoute, private api: ApiClientService) {
    this.route.paramMap.subscribe(params => {
      this.serviceId = params.get('id') || undefined;
      console.log(this.serviceId);
    });
    this.api.get("users").then((users) => {
      this.users = users;
      console.log(this.users);
    });
  }

  ngOnInit(): void { }

}
