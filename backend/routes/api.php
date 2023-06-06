<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScheduleSlotsController;
use App\Http\Controllers\AvailabilityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Telegram\Bot\Laravel\Http\Middleware\ValidateWebhook;
use Telegram\Bot\Laravel\Http\Controllers\WebhookController;

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

Route::middleware('auth:web')->group( function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/me', [AuthController::class, 'me']);
    
    Route::post('/impersonate/{user}', [AuthController::class, 'impersonate']);
    Route::post('/stop_impersonating', [AuthController::class, 'stopImpersonating']);

    Route::get('/list', [UserController::class, 'index']);

    Route::get('/schedules', [ScheduleSlotsController::class, 'index']);
    Route::post('/schedules', [ScheduleSlotsController::class, 'store']);

    Route::get('/availability', [AvailabilityController::class, 'get']);
    Route::post('/availability', [AvailabilityController::class, 'updateAvailability']);
    Route::post('/manual_mode', [AvailabilityController::class, 'updateAvailabilityManualMode']);

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

//TODO: remove this and open issue on https://github.com/telegram-bot-sdk/laravel since named route not working
Route::group(['middleware' => ValidateWebhook::class], function (): void {
    Route::post('/telegram/{bot}/webhook', Telegram\Bot\Laravel\Http\Controllers\WebhookController::class)->name('telegram.bot.webhook');
});
