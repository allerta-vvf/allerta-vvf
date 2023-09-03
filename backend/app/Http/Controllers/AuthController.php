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
        if(
            method_exists(auth()->user(), 'currentAccessToken') &&
            method_exists(auth()->user()->currentAccessToken(), 'delete')
        ) {
            auth()->user()->currentAccessToken()->delete();
        }
        auth()->guard('api')->logout();
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
