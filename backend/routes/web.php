<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

use App\Models\User;
use App\Services\Logger;

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

//Generate various routes with same middleware auth
Route::group(['middleware' => ['auth']], function () {
    Route::get('/', function () {
        User::where('id', auth()->user()->id)->update(['last_access' => now()]);
        return view('home', [
            'available' => Auth::user()->available,
            'availability_manual_mode' => Auth::user()->availability_manual_mode,
            'users' => User::getAvailableUsers(Auth::user()->isAbleTo("users-read"))
        ]);
    })->name('home');
    Route::get('/services', function () {
        // Query services with related data, similar to API controller
        $services = \App\Models\Service::with(['chief', 'type'])
            ->orderBy('start', 'desc')
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'code' => $service->code,
                    'type' => $service->type ? $service->type->name : '',
                    'chief' => $service->chief ? ($service->chief->name . ' ' . $service->chief->surname) : '',
                    'start' => $service->start,
                    'end' => $service->end,
                    'notes' => $service->notes,
                ];
            });
        return view('services', ['services' => $services]);
    })->name('services');
    Route::get('/services/create', function () {
        return view('service_form');
    })->name('services.create');
    Route::get('/services/{id}', function ($id) {
        // You can pass the service to the view for editing in the future
        return view('service_form', ['serviceId' => $id]);
    })->name('services.edit');
    Route::get('/trainings', function () {
        // Query trainings with related data, similar to services
        $trainings = \App\Models\Training::with(['chief'])
            ->orderBy('start', 'desc')
            ->get()
            ->map(function ($training) {
                return [
                    'id' => $training->id,
                    'name' => $training->name,
                    'start' => $training->start,
                    'end' => $training->end,
                    'chief' => $training->chief ? ($training->chief->name . ' ' . $training->chief->surname) : '',
                    'crew' => $training->crew,
                    'place' => $training->place,
                    'notes' => $training->notes,
                ];
            });
        return view('trainings', ['trainings' => $trainings]);
    })->name('trainings');
    Route::get('/trainings/create', function () {
        return view('training_form');
    })->name('trainings.create');
    Route::get('/trainings/{id}', function ($id) {
        // You can pass the training to the view for editing in the future
        return view('training_form', ['trainingId' => $id]);
    })->name('trainings.edit');
    Route::get('/logs', function () {
        return view('logs');
    })->name('logs');
    Route::get('/stats', function () {
        return view('stats');
    })->name('stats');
    Route::get('/admin', function () {
        return view('admin');
    })->name('admin');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'username' => 'required|string|exists:users,username|max:255',
        'password' => 'required',
    ]);

    $user = User::where('username', $request->username)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return back()->withErrors([
            'username' => ['Credenziali inserite non valide.'],
        ])->onlyInput('username');
    }

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        Logger::log("Login", $user, $user);
        return redirect(URL::r('home'));
    }

    return back()->withErrors([
        'username' => ['Credenziali inserite non valide.'],
    ])->onlyInput('username');
})->name('login.post');

Route::get('/logout', function (Request $request) {
    Auth::logout();
    
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    
    return redirect(URL::r('home'));
})->name('logout');
