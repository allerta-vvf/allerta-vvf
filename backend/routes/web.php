<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

use App\Models\User;
use App\Utils\Logger;

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
        $requestedCols = ['id', 'chief', 'last_access', 'name', 'surname', 'available', 'driver', 'services', 'availability_minutes'];
        if(auth()->user()->isAbleTo("users-read")) $requestedCols[] = "phone_number";

        User::where('id', auth()->user()->id)->update(['last_access' => now()]);

        $list = User::where('hidden', 0)
            ->select($requestedCols)
            ->orderBy('available', 'desc')
            ->orderBy('chief', 'desc')
            ->orderBy('driver', 'desc')
            ->orderBy('services', 'asc')
            ->orderBy('trainings', 'desc')
            ->orderBy('availability_minutes', 'desc')
            ->orderBy('name', 'asc')
            ->orderBy('surname', 'asc')
            ->get();

        $now = now();
        foreach($list as $user) {
            //Add online status
            $user->online = !is_null($user->last_access) && $user->last_access->diffInSeconds($now) < 30;
            //Delete last_access
            unset($user->last_access);
        }

        return view('home', [
            'available' => Auth::user()->available,
            'availability_manual_mode' => Auth::user()->availability_manual_mode,
            'users' => $list
        ]);
    })->name('home');
    Route::get('/services', function () {
        return view('services');
    })->name('services');
    Route::get('/trainings', function () {
        return view('trainings');
    })->name('trainings');
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
