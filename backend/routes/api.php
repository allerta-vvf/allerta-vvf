<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScheduleSlotsController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\DocumentsController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\PlacesController;
use App\Http\Controllers\ServiceTypeController;
use App\Http\Controllers\TrainingCourseTypeController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use \Matthewbdaly\ETagMiddleware\ETag;

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

Route::middleware('auth:sanctum')->group( function () {
    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/impersonate/{user}', [AuthController::class, 'impersonate']);
    Route::post('/stop_impersonating', [AuthController::class, 'stopImpersonating']);

    Route::post('/refresh_token', [AuthController::class, 'refreshToken']);

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/me', [AuthController::class, 'me']);

    Route::get('/list', [UserController::class, 'index'])->middleware(ETag::class);

    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);

    Route::post('/documents/driving_license', [DocumentsController::class, 'uploadDrivingLicenseScan']);
    Route::post('/documents/training_course', [DocumentsController::class, 'addTrainingCourse']);
    Route::post('/documents/medical_examination', [DocumentsController::class, 'addMedicalExamination']);

    Route::get('/training_course_types', [TrainingCourseTypeController::class, 'index']);
    Route::post('/training_course_types', [TrainingCourseTypeController::class, 'create']);

    Route::get('/schedules', [ScheduleSlotsController::class, 'index']);
    Route::post('/schedules', [ScheduleSlotsController::class, 'store']);

    Route::get('/availability', [AvailabilityController::class, 'get'])->middleware(ETag::class);
    Route::post('/availability', [AvailabilityController::class, 'updateAvailability']);
    Route::post('/manual_mode', [AvailabilityController::class, 'updateAvailabilityManualMode']);

    Route::get('/alerts', [AlertController::class, 'index'])->middleware(ETag::class);
    Route::post('/alerts', [AlertController::class, 'store']);
    Route::get('/alerts/{id}', [AlertController::class, 'show'])->middleware(ETag::class);
    Route::patch('/alerts/{id}', [AlertController::class, 'update']);
    Route::post('/alerts/{id}/response', [AlertController::class, 'setResponse']);

    Route::get('/services', [ServiceController::class, 'index'])->middleware(ETag::class);
    Route::post('/services', [ServiceController::class, 'createOrUpdate']);
    Route::get('/services/{id}', [ServiceController::class, 'show']);
    Route::delete('/services/{id}', [ServiceController::class, 'destroy']);

    Route::get('/service_types', [ServiceTypeController::class, 'index']);
    Route::post('/service_types', [ServiceTypeController::class, 'create']);
    Route::get('/places/search', [PlacesController::class, 'search']);
    Route::get('/places/{id}', [PlacesController::class, 'show']);

    Route::get('/trainings', [TrainingController::class, 'index'])->middleware(ETag::class);
    Route::post('/trainings', [TrainingController::class, 'createOrUpdate']);
    Route::get('/trainings/{id}', [TrainingController::class, 'show']);
    Route::delete('/trainings/{id}', [TrainingController::class, 'destroy']);

    Route::get('/logs', [LogsController::class, 'index'])->middleware(ETag::class);

    Route::get('/stats/services', [StatsController::class, 'services']);

    Route::post('/telegram_login_token', [TelegramController::class, 'loginToken']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/admin/info', [AdminController::class, 'getInfo']);

    Route::get('/admin/db', [AdminController::class, 'getDBData']);
    Route::post('/admin/runMigrations', [AdminController::class, 'runMigrations']);
    Route::post('/admin/runSeeding', [AdminController::class, 'runSeeding']);

    Route::get('/admin/maintenanceMode', [AdminController::class, 'getMaintenanceMode']);
    Route::post('/admin/maintenanceMode', [AdminController::class, 'updateMaintenanceMode']);

    Route::get('/admin/permissionsAndRoles', [AdminController::class, 'getPermissionsAndRoles']);
    Route::post('/admin/roles', [AdminController::class, 'updateRoles']);
});

Route::middleware('signed')->group( function () {
    Route::get('/documents/driving_license/{uuid}', [DocumentsController::class, 'serveDrivingLicenseScan'])->name('driving_license_scan_serve');
    Route::get('/documents/training_course/{uuid}', [DocumentsController::class, 'serveTrainingCourse'])->name('training_course_serve');
    Route::get('/documents/medical_examination/{uuid}', [DocumentsController::class, 'serveMedicalExamination'])->name('medical_examination_serve');
});

Route::get('/owner_image', function() {
    return response()
      ->file(
        resource_path('images') . DIRECTORY_SEPARATOR . config("features.owner_image"),
        ['Cache-control' => 'max-age=2678400']
    );
});

Route::get('/ping', function() {
    return response()->json([
        'message' => 'pong'
    ]);
});

Route::post('/cron/execute', function(Request $request) {
    //Go to app/Console/Kernel.php to view schedules
    if(config('cron.external_cron_enabled') && $request->header('Cron') == config('cron.execution_code')) {
        Artisan::call('schedule:run');
    } else {
        return response('Access Denied', 403);
    }
});
