import { Component, OnInit } from '@angular/core';
import { ApiClientService } from 'src/app/_services/api-client.service';
import { TranslateService } from '@ngx-translate/core';
import Swal from 'sweetalert2';

interface RolePermissionPair {
  roleId: number;
  permissionId: number;
}

@Component({
  selector: 'app-admin-roles',
  templateUrl: './admin-roles.component.html',
  styleUrls: ['./admin-roles.component.scss']
})
export class AdminRolesComponent implements OnInit {
  permissions: any;
  roles: any;
  originalRoles: any;

  roleChanges: RolePermissionPair[] = [];
  roleChangesSubmitting: boolean = false;

  constructor(private api: ApiClientService, private translateService: TranslateService) { }

  getPermissionsAndRoles() {
    this.api.get('admin/permissionsAndRoles').then((res: any) => {
      this.permissions = res.permissions;
      this.roles = res.roles;
      this.originalRoles = JSON.parse(JSON.stringify(res.roles)); // Deep copy
      this.roleChanges = [];
      console.log(res);
    }).catch((err: any) => {
      console.error(err);
      Swal.fire({
        title: this.translateService.instant("error_title"),
        text: err.error.message,
        icon: 'error',
        confirmButtonText: 'Ok'
      });
    }).finally(() => {
      this.roleChangesSubmitting = false; //Because after submit response, data is reloaded
    });
  }

  ngOnInit(): void {
    this.getPermissionsAndRoles();
  }

  togglePermission(role: any, permission: any) {
    const index = role.permissions.indexOf(role.permissions.find((p: any) => p.id == permission.id));
    if (index > -1) {
      // If the role has the permission, remove it
      role.permissions.splice(index, 1);
    } else {
      // If the role doesn't have the permission, add it
      role.permissions.push(permission);
    }
    if(
      this.originalRoles.find((r: any) => r.id == role.id).permissions.length !== this.roles.find((r: any) => r.id == role.id).permissions.length &&
      this.roleChanges.find((r: any) => r.roleId == role.id && r.permissionId == permission.id) == undefined
    ) {

      this.roleChanges.push({roleId: role.id, permissionId: permission.id});
    } else {
      let roleChange = this.roleChanges.find((r: any) => r.roleId == role.id && r.permissionId == permission.id);
      if(roleChange) this.roleChanges.splice(this.roleChanges.indexOf(roleChange), 1);
    }
    console.log(this.roleChanges);
  }

  doesRoleHavePermission(role: any, permissionId: number) {
    return role.permissions.some((p: any) => p.id == permissionId)
  }

  saveRoleChanges() {
    this.roleChangesSubmitting = true;
    this.api.post('admin/roles', {
      changes: this.roleChanges
    }).then((res: any) => {
      console.log(res);
      this.getPermissionsAndRoles();
    }).catch((err: any) => {
      this.roleChangesSubmitting = false;
      console.error(err);
      Swal.fire({
        title: this.translateService.instant("error_title"),
        text: err.error.message,
        icon: 'error',
        confirmButtonText: 'Ok'
      });
    });
  }
}
