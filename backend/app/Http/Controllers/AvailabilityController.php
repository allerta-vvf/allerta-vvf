<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Utils\Availability;

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
            if(!$request->user()->hasPermission("users-read")) abort(401);
            $user = User::find($request->input("id"));
        } else {
            $user = $request->user();
        }

        return Availability::updateAvailability($user, $request->input("available", false));
    }

    public function updateAvailabilityManualMode(Request $request)
    {
        return Availability::updateAvailabilityManualMode($request->user(), $request->input("manual_mode", false));
    }
}
