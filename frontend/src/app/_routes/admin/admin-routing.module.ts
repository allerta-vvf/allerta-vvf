import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AdminComponent } from './admin.component';
import { AuthorizeGuard } from 'src/app/_guards/authorize.guard';

const routes: Routes = [{
    path: '',
    component: AdminComponent,
    children: [
        {
            path: 'info',
            loadChildren: () => import('./info/admin-info.module').then(m => m.AdminInfoModule),
            canActivate: [AuthorizeGuard],
            data: {permissionsRequired: ['admin-read', 'admin-info-read']}
        },
        {
            path: 'maintenance',
            loadChildren: () => import('./maintenance/admin-maintenance.module').then(m => m.AdminMaintenanceModule),
            canActivate: [AuthorizeGuard],
            data: {permissionsRequired: ['admin-read', 'admin-maintenance-read']}
        },
        {
            path: 'options',
            loadChildren: () => import('./options/admin-options.module').then(m => m.AdminOptionsModule),
            canActivate: [AuthorizeGuard],
            data: {permissionsRequired: ['admin-read', 'admin-options-read']}
        },
        {
            path: 'roles',
            loadChildren: () => import('./roles/admin-roles.module').then(m => m.AdminRolesModule),
            canActivate: [AuthorizeGuard],
            data: {permissionsRequired: ['admin-read', 'admin-roles-read']}
        }
    ]
}];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminRoutingModule { }
