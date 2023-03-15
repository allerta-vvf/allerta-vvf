<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AvailabilityController extends Controller
{
    public function get(Request $request)
    {
        return [
            "available" => $request->user()->available,
            "manual_mode" => $request->user()->availability_manual_mode
        ];
    }

    public function updateAvailability(Request $request)
    {
        if($request->input("id")) {
            $user = User::find($request->input("id"));
        } else {
            $user = $request->user();
        }

        $user->available = $request->input("available", false);
        $user->availability_manual_mode = true;
        $user->save();

        return [
            "updated_user_id" => $user->id,
            "updated_user_name" => $user->name
        ];
    }

    public function updateAvailabilityManualMode(Request $request)
    {
        $user = $request->user();
        $user->availability_manual_mode = $request->input("manual_mode", false);
        $user->save();

        return;
    }
}
