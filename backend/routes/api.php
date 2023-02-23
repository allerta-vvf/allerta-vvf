<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AvailabilityController;

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
