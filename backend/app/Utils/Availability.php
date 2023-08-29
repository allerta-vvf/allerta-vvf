<?php

namespace App\Utils;

use App\Models\User;

class Availability {
    public static function updateAvailability(User|int $id, bool $available)
    {
        if(is_int($id)) {
            $user = User::find($id);
        } else {
            $user = $id;
        }

        $last_availability = $user->available;
        $last_availability_change = $user->last_availability_change;
        $new_last_availability_change = now();

        //Increment only if the user was available and now is not
        if(!is_null($last_availability_change) && $last_availability && !$available) {
            $diff = $new_last_availability_change->diffInMinutes($last_availability_change);
            if($diff > 0) $user->availability_minutes += $diff;
        }

        $user->available = $available;
        $user->availability_manual_mode = true;
        $user->last_availability_change = $new_last_availability_change;
        $user->save();

        return [
            "updated_user_id" => $user->id,
            "updated_user_name" => $user->name
        ];
    }

    public static function updateAvailabilityManualMode(User|int $id, bool $manual_mode)
    {
        if(is_int($id)) {
            $user = User::find($id);
        } else {
            $user = $id;
        }
        $user->availability_manual_mode = $manual_mode;
        $user->save();

        return;
    }
}