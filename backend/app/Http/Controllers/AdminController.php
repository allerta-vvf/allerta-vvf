<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function getDBData() {
        if(!request()->user()->hasPermission("admin-maintenance-read")) abort(401);
        
        Artisan::call('db:show', ['--json' => true, '--counts' => true]);
        $output = Artisan::output();
        $parsedOutput = json_decode($output, true);

        $platformConfigUnsets = [
            'options',
            'collation',
            'unix_socket',
            'url',
            'strict',
            'engine',
            'timezone'
        ];
        foreach($parsedOutput['platform']['config'] as $key => $value) {
            if(in_array($key, $platformConfigUnsets)) unset($parsedOutput['platform']['config'][$key]);
        }

        $globalSize = 0;
        foreach($parsedOutput['tables'] as $table) {
            $globalSize += $table['size'];
        }
        $parsedOutput['global_size'] = $globalSize;

        Artisan::call('migrate:status');
        $output = Artisan::output();

        // Parse the output into an array
        $lines = explode("\n", $output);
        $migrations = [];
        $migrationsRan = 0;
        $migrationsPending = 0;
        foreach ($lines as $line) {
            if (str_contains($line, ' Ran')) {
                $migrationName = trim(substr($line, 0, strpos($line, '...')));
                $migrations[] = ['name' => $migrationName, 'status' => 'Ran'];
                $migrationsRan++;
            } elseif (str_contains($line, ' Pending')) {
                $migrationName = trim(substr($line, 0, strpos($line, '...')));
                $migrations[] = ['name' => $migrationName, 'status' => 'Pending'];
                $migrationsPending++;
            }
        }

        $parsedOutput['migrations'] = $migrations;
        $parsedOutput['migrations_ran'] = $migrationsRan;
        $parsedOutput['migrations_pending'] = $migrationsPending;

        return response()->json($parsedOutput);
    }

    public function runMigrations() {
        if(!request()->user()->hasPermission("admin-maintenance-update")) abort(401);
        
        Artisan::call('migrate', ['--force' => true]);
        return response()->json([
            'message' => 'Migrations ran successfully'
        ]);
    }

    public function runSeeding() {
        if(!request()->user()->hasPermission("admin-maintenance-update")) abort(401);
        
        Artisan::call('db:seed', ['--force' => true]);
        return response()->json([
            'message' => 'Seeders ran successfully'
        ]);
    }

    public function getMaintenanceMode() {
        if(!request()->user()->hasPermission("admin-maintenance-read")) abort(401);
        
        if (App::isDownForMaintenance()) {
            return response()->json(['enabled' => true]);
        } else {
            return response()->json(['enabled' => false]);
        }
    }

    public function updateMaintenanceMode(Request $request) {
        if(!request()->user()->hasPermission("admin-maintenance-update")) abort(401);
        
        $request->validate([
            'enabled' => 'required|boolean'
        ]);

        $maintenanceMode = $request->input('enabled');
        if($maintenanceMode) {
            $uuid = Str::uuid()->toString();
            $secret = "api/admin/bypass_maintenance/".$uuid;

            Artisan::call('down', ['--secret' => $secret]);

            return response()->json([
                'message' => 'Maintenance mode enabled',
                'secret_endpoint' => substr($secret, 4)
            ]);
        } else {
            Artisan::call('up');

            return response()->json([
                'message' => 'Maintenance mode disabled'
            ]);
        }
    }

    public function getPermissionsAndRoles() {
        if(!request()->user()->hasPermission("admin-roles-read")) abort(401);
        return response()->json([
            'permissions' => \App\Models\Permission::orderBy('name')->get(),
            'roles' => \App\Models\Role::with('permissions:id,name')->get()
        ]);
    }

    public function updateRoles(Request $request) {
        if(!request()->user()->hasPermission("admin-roles-update")) abort(401);

        $request->validate([
            'changes' => 'required|array',
            'changes.*.roleId' => 'required|integer|exists:roles,id',
            'changes.*.permissionId' => 'required|integer|exists:permissions,id'
        ]);

        $roles = $request->input('changes');
        foreach($roles as $role) {
            $roleModel = \App\Models\Role::find($role['roleId']);
            //If the role already has the permission, remove it, otherwise add it
            if($roleModel->permissions()->where('id', $role['permissionId'])->exists()) {
                $roleModel->permissions()->detach([$role['permissionId']]);
            } else {
                $roleModel->permissions()->attach([$role['permissionId']]);
            }
        }

        return response()->json([
            'message' => 'Roles updated successfully'
        ]);
    }
}
