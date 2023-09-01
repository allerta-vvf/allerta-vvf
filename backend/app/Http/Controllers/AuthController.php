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
        //TODO: https://stackoverflow.com/a/73980629
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
        //TODO: https://stackoverflow.com/a/73980629
        Logger::log("Logout");
        auth('web')->logout();
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
        $request->user()->leaveImpersonation();
        return;
    }
}
