<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Utils\Logger;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        if(!$request->user()->hasPermission("users-create")) abort(401);
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
        $request->validate([
			'username' => 'required|string|exists:users,username|max:255',
			'password' => 'required',
		]);

		$user = User::where('username', $request->username)->first();

		if (! $user || ! Hash::check($request->password, $user->password)) {
			throw ValidationException::withMessages([
				'username' => ['Credenziali inserite non valide.'],
			]);
		}

        $user = User::where('username', $request['username'])->firstOrFail();

        if($request->input('use_sessions', false)) {
            $request->session()->regenerate();
            auth()->guard('api')->login($user);
            $token = null;
        } else {
            $token = $user->createToken('auth_token')->plainTextToken;
        }

        Logger::log("Login", $user, $user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'auth_type' => is_null($token) ? 'session' : 'token'
        ]);
    }

    public function logout(Request $request)
    {
        Logger::log("Logout");

        if(
            method_exists($request->user(), 'currentAccessToken') &&
            method_exists($request->user()->currentAccessToken(), 'delete')
        ) {
            $request->user()->currentAccessToken()->delete();
        } else {
            auth()->guard('api')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

		return response()->json(null, 200);
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
        $authUser = User::find($request->user()->id);
        if(!$authUser->canImpersonate()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        if(
            method_exists($request->user(), 'currentAccessToken') &&
            method_exists($request->user()->currentAccessToken(), 'delete')
        ) {
            $request->user()->currentAccessToken()->delete();
        } else {
            auth()->guard('api')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
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
        $manager = app('impersonate');

        $impersonatorId = $manager->getImpersonatorId();
        $manager->leave();
        $manager->clear();
        $impersonator = User::find($impersonatorId);

        if(
            method_exists($request->user(), 'currentAccessToken') &&
            method_exists($request->user()->currentAccessToken(), 'delete')
        ) {
            $request->user()->currentAccessToken()->delete();
        } else {
            auth()->guard('api')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if($request->input('use_sessions', false)) {
            $request->session()->regenerate();
            auth()->guard('api')->login($impersonator);
            $token = null;
        } else {
            $token = $impersonator->createToken('auth_token')->plainTextToken;
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function refreshToken(Request $request)
    {
        if(
            !method_exists($request->user(), 'currentAccessToken') ||
            !method_exists($request->user()->currentAccessToken(), 'delete')
        ) return;

        $user = $request->user();
        $user->currentAccessToken()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
}
