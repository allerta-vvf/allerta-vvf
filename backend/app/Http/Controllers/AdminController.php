<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class AdminController extends Controller
{
    public function getInfo() {
        if(!request()->user()->hasPermission("admin-info-read")) abort(401);

        return response()->json([
            'users' => User::select('id', 'name', 'surname', 'last_access', 'created_at')
                ->orderBy('last_access', 'desc')
                ->get()
        ]);
    }

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

    public function getJobsList() {
        if(!request()->user()->hasPermission("admin-maintenance-read")) abort(401);
        
        $jobPath = app_path('Jobs');
        $jobs = [];

        $files = scandir($jobPath);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == 'php') {
                $jobs[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        return response()->json($jobs);
    }

    public function runJob(Request $request) {
        if(!request()->user()->hasPermission("admin-maintenance-update")) abort(401);
        
        $request->validate([
            'job' => 'required|string'
        ]);

        Artisan::call('schedule:test', ['--name' => "App\\Jobs\\".$request->input('job')]);
        $output = Artisan::output();
        
        if(str_contains($output, 'No matching scheduled command found.')) {
            return response()->json([
                'message' => 'Job not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Job ran successfully'
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

    public function runOptimization() {
        if(!request()->user()->hasPermission("admin-maintenance-update")) abort(401);
        
        $commands = [
            'config:cache',
            'event:cache',
            'route:cache'
        ];

        foreach($commands as $command) {
            Artisan::call($command);
        }

        return response()->json([
            'message' => 'Optimization ran successfully'
        ]);
    }

    public function clearOptimization() {
        if(!request()->user()->hasPermission("admin-maintenance-update")) abort(401);

        //Check if .env file exists. If not, abort the operation
        if(!file_exists(base_path('.env'))) {
            return response()->json([
                'message' => 'WARNING!! Environment file not found'
            ], 400);
        }
        
        Artisan::call('optimize:clear');

        return response()->json([
            'message' => 'Optimization cleared successfully'
        ]);
    }

    public function clearCache() {
        if(!request()->user()->hasPermission("admin-maintenance-update")) abort(401);
        
        Artisan::call('cache:clear');

        return response()->json([
            'message' => 'Cache cleared successfully'
        ]);
    }

    public function encryptEnvironment(Request $request) {
        if(!request()->user()->hasPermission("admin-maintenance-update")) abort(401);
        $request->validate([
            'key' => 'required|string|min:6'
        ]);

        $key = "base64:".base64_encode(hash('sha256', $request->input('key'), true));
        
        Artisan::call('env:encrypt', ['--force' => true, '--no-interaction' => true, '--key' => $key]);
        //Check if "ERROR" is in the output
        $output = Artisan::output();
        if(str_contains($output, 'ERROR')) {
            return response()->json([
                'message' => str_replace('ERROR ', '', $output)
            ], 400);
        }
        
        return response()->json([
            'message' => 'Environment encrypted successfully'
        ]);
    }

    public function decryptEnvironment(Request $request) {
        if(!request()->user()->hasPermission("admin-maintenance-update")) abort(401);
        $request->validate([
            'key' => 'required|string|min:6'
        ]);

        $key = "base64:".base64_encode(hash('sha256', $request->input('key'), true));
        
        Artisan::call('env:decrypt', ['--force' => true, '--no-interaction' => true, '--key' => $key]);
        //Check if "ERROR" is in the output
        $output = Artisan::output();
        if(str_contains($output, 'ERROR')) {
            return response()->json([
                'message' => str_replace('ERROR ', '', $output)
            ], 400);
        }

        //Delete .env.encrypted file if exists
        if(file_exists(base_path('.env.encrypted'))) unlink(base_path('.env.encrypted'));

        return response()->json([
            'message' => 'Environment decrypted successfully'
        ]);
    }

    public function deleteEnvironment() {
        if(!request()->user()->hasPermission("admin-maintenance-update")) abort(401);

        if(!file_exists(base_path('bootstrap/cache/config.php'))) Artisan::call('config:cache');

        //Delete .env file if exists
        if(file_exists(base_path('.env'))) unlink(base_path('.env'));

        return response()->json([
            'message' => 'Environment file deleted successfully'
        ]);
    }
    
    public function getTelegramBotDebugInfo() {
        if(!request()->user()->hasPermission("admin-maintenance-update")) abort(401);
        
        Artisan::call('telegraph:debug-webhook');
        $output = Artisan::output();

        // Convert the text to a query string format
        $queryString = str_replace([': ', "\n"], ['=', '&'], $output);

        // Parse the query string
        parse_str($queryString, $result);

        foreach($result as $key => &$value) {
            $key = trim($key);
            $value = trim($value);
            if($value === "no") $value = false;
            if($value === "si" || $value === "sì" || $value === "yes") $value = true;
        }
        unset($value);

        return response()->json($result);
    }

    public function setTelegramWebhook() {
        if(!request()->user()->hasPermission("admin-maintenance-update")) abort(401);
        
        Artisan::call('telegraph:set-webhook');

        return response()->json([
            'message' => 'Webhook set successfully'
        ]);
    }

    public function unsetTelegramWebhook() {
        if(!request()->user()->hasPermission("admin-maintenance-update")) abort(401);
        
        Artisan::call('telegraph:unset-webhook');

        return response()->json([
            'message' => 'Webhook unset successfully'
        ]);
    }

    public function getPermissionsAndRoles() {
        if(!request()->user()->hasPermission("admin-roles-read")) abort(401);
        return response()->json([
            'permissions' => Permission::orderBy('name')->get(),
            'roles' => Role::with('permissions:id,name')->get()
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
            $roleModel = Role::find($role['roleId']);
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
