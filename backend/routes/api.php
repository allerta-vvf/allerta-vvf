<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AvailabilityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group( function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/me', [AuthController::class, 'me']);

    Route::get('/list', [UserController::class, 'index']);

    Route::get('/availability', [AvailabilityController::class, 'get']);
    Route::post('/availability', [AvailabilityController::class, 'updateAvailability']);
    Route::post('/manual_mode', [AvailabilityController::class, 'updateAvailabilityManualMode']);

    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::post('/cron/execute', function(Request $request) {
    //Go to app/Console/Kernel.php to view schedules
    if(config('cron.external_cron_enabled') && $request->header('Cron') == config('cron.execution_code')) {
        Artisan::call('schedule:run');
    } else {
        return response('Access Denied', 403);
    }
});
