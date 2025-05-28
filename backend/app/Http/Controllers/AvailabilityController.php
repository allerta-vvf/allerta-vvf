<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\Availability;

class AvailabilityController extends Controller
{
    /**
     * Get the availability status of the user. If in "manual mode", the availability is not automatically updated following timetables.
     */
    public function get(Request $request)
    {
        return [
            /**
             * @var boolean
             */
            "available" => $request->user()->available,
            /**
             * @var boolean
             */
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

        $request->validate([
            /**
             * The id of the user to update the availability status. If not provided, the current user will be used
             * @var int|null
             * @example null
             */
            'id' => ['nullable', 'integer'],
            /**
             * The availability status of the user
             * @var boolean
             * @example true
             */
            'available' => ['required', 'bool'],
        ]);

        $opReturn = Availability::updateAvailability($user, $request->input("available", false));
        return [
            /**
             * @var int
             * @example 1
             */
            "updated_user_id" => $opReturn["updated_user_id"],
            /**
             * @example Nome
             */
            "updated_user_name" => $opReturn["updated_user_name"]
        ];
    }

    /**
     * Update the availability manual mode status of the current user
     */
    public function updateAvailabilityManualMode(Request $request)
    {
        $request->validate([
            'manual_mode' => ['required', 'bool']
        ]);

        Availability::updateAvailabilityManualMode($request->user(), $request->input("manual_mode", false));

        return response()->noContent();
    }
}
