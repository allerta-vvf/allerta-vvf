import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { TabDirective } from 'ngx-bootstrap/tabs';
import { TranslateService } from '@ngx-translate/core';
import { AuthService } from 'src/app/_services/auth.service';

interface ITab {
    title: string;
    id: string;
    active: boolean;
    permissionsRequired: string[];
}

@Component({
  selector: 'app-admin',
  templateUrl: './admin.component.html',
  styleUrls: ['./admin.component.scss']
})
export class AdminComponent implements OnInit {
  currRoute: string | undefined = '';
  tabs: ITab[] = [
    { title: 'info', id: 'info', active: false, permissionsRequired: ['admin-read', 'admin-info-read'] },
    { title: 'maintenance', id: 'maintenance', active: false, permissionsRequired: ['admin-read', 'admin-maintenance-read'] },
    { title: 'roles', id: 'roles', active: false, permissionsRequired: ['admin-read', 'admin-roles-read'] }
  ];

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private translate: TranslateService,
    private auth: AuthService
  ) {
    // Filter out tabs that the user doesn't have permission to see
    this.tabs = this.tabs.filter(t => !t.permissionsRequired.some(p => !this.auth.profile.can(p)));

    // Translate tab titles
    this.tabs.forEach((t) => {
        this.translate.get(`admin.${t.title}`).subscribe((res: string) => {
            t.title = res;
        });
    });
  }

  ngOnInit(): void {
    this.currRoute = this.route?.snapshot?.firstChild?.routeConfig?.path;
    if (this.currRoute) {
      const tab = this.tabs.find(t => t.id === this.currRoute);
      if (tab) {
        tab.active = true;
      }
    } else if (this.tabs.length > 0) {
        this.router.navigate(["/admin", this.tabs[0].id]);
        this.currRoute = this.tabs[0].id;
        this.tabs[0].active = true;
    }
  }

  routeChange(data: TabDirective) {
    if(!data.id || data.id == this.currRoute) return;
    this.router.navigate(["/admin", data.id]);
    this.currRoute = data.id;
  }

}
