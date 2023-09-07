<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScheduleSlotsController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\PlacesController;
use App\Http\Controllers\ServiceTypeController;
use App\Http\Controllers\TrainingController;
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

Route::post('/login', [AuthController::class, 'login']);
Route::post('/impersonate/{user}', [AuthController::class, 'impersonate']);
Route::post('/stop_impersonating', [AuthController::class, 'stopImpersonating']);

Route::middleware('auth:sanctum')->group( function () {
    //Route::post('/register', [AuthController::class, 'register']); //TODO: replace with admin only route

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/me', [AuthController::class, 'me']);

    Route::get('/list', [UserController::class, 'index']);

    Route::get('/schedules', [ScheduleSlotsController::class, 'index']);
    Route::post('/schedules', [ScheduleSlotsController::class, 'store']);

    Route::get('/availability', [AvailabilityController::class, 'get']);
    Route::post('/availability', [AvailabilityController::class, 'updateAvailability']);
    Route::post('/manual_mode', [AvailabilityController::class, 'updateAvailabilityManualMode']);

    Route::get('/services', [ServiceController::class, 'index']);
    Route::post('/services', [ServiceController::class, 'createOrUpdate']);
    Route::get('/services/{id}', [ServiceController::class, 'show']);
    Route::delete('/services/{id}', [ServiceController::class, 'destroy']);

    Route::get('/service_types', [ServiceTypeController::class, 'index']);
    Route::post('/service_types', [ServiceTypeController::class, 'create']);
    Route::get('/places/search', [PlacesController::class, 'search']);
    Route::get('/places/{id}', [PlacesController::class, 'show']);

    Route::get('/trainings', [TrainingController::class, 'index']);
    Route::post('/trainings', [TrainingController::class, 'createOrUpdate']);
    Route::get('/trainings/{id}', [TrainingController::class, 'show']);
    Route::delete('/trainings/{id}', [TrainingController::class, 'destroy']);

    Route::get('/logs', [LogsController::class, 'index']);

    Route::post('/telegram_login_token', [TelegramController::class, 'loginToken']);

    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::get('/owner_image', function() {
    return response()
      ->file(
        resource_path('images') . DIRECTORY_SEPARATOR . config("features.owner_image"),
        ['Cache-control' => 'max-age=2678400']
    );
});

Route::post('/cron/execute', function(Request $request) {
    //Go to app/Console/Kernel.php to view schedules
    if(config('cron.external_cron_enabled') && $request->header('Cron') == config('cron.execution_code')) {
        Artisan::call('schedule:run');
    } else {
        return response('Access Denied', 403);
    }
});
