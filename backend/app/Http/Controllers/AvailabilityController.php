<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Utils\Availability;

class AvailabilityController extends Controller
{
    /**
     * Get the availability status of the user
     */
    public function get(Request $request)
    {
        return [
            "available" => $request->user()->available,
            "manual_mode" => $request->user()->availability_manual_mode
        ];
    }

    /**
     * Update the availability status of an user (or the current user)
     */
    public function updateAvailability(Request $request)
    {
        if($request->input("id") && $request->user()->id != $request->input("id")) {
            if(!$request->user()->hasPermission("users-read")) abort(401);
            $user = User::find($request->input("id"));
        } else {
            $user = $request->user();
        }

        return Availability::updateAvailability($user, $request->input("available", false));
    }

    /**
     * Update the availability manual mode status of the current user
     */
    public function updateAvailabilityManualMode(Request $request)
    {
        return Availability::updateAvailabilityManualMode($request->user(), $request->input("manual_mode", false));
    }
}
