<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Utils\Logger;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'username' => $validatedData['username'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        Logger::log("Creato utente $user->name ($user->username)", $user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = User::where('username', $request['username'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        Logger::log("Login", $user, $user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        Logger::log("Logout");

        auth()->guard('api')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        /**
         * Only works with cookie auth, Laravel authentication sucks
         * I just want to auth users in the webapp using cookies and
         * using Bearer tokens when making API calls from outside the frontend
         * (for example mobile apps, external clients etc.)
         * but it's not possible without a lot of hacks.
         * Even this way, it doesn't work 100% well, with random 419 errors
         * and other stuff.
         * I'm wasting too much time on this.
         * Users are authenticated, that's enough for now.
         * Logout doesn't work very well even this cookies to be honest.
         * I'm not sure if it's a Laravel bug or what.
         * I don't know, I should ask online.
         * 
         * TODO: https://github.com/laravel/sanctum/issues/80
         */

        return;
    }

    public function me(Request $request)
    {
        $impersonateManager = app('impersonate');
        return [
            ...$request->user()->toArray(),
            "permissions" => array_map(function($p) {
                return $p["name"];
            }, $request->user()->allPermissions()->toArray()),
            "impersonating_user" => $impersonateManager->isImpersonating(),
            "impersonator_id" => $impersonateManager->getImpersonatorId()
        ];
    }

    public function impersonate(Request $request, $user)
    {
        if(!$request->user()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        $authUser = User::find($request->user()->id);
        if(!$authUser->canImpersonate()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $impersonatedUser = User::find($user);
        $request->user()->impersonate($impersonatedUser);
        $token = $impersonatedUser->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
    
    public function stopImpersonating(Request $request)
    {
        if(!$request->user()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->user()->leaveImpersonation();
        return;
    }
}
