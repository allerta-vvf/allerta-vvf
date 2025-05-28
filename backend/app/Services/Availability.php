<?php

namespace App\Services;

use App\Models\User;
use App\Services\TelegramBot;
use App\Services\Logger;

class Availability {
    public static function updateAvailability(User|int $id, bool $available, bool $fromTelegram = false)
    {
        if(is_int($id)) {
            $user = User::find($id);
        } else {
            $user = $id;
        }

        $available_users_count_before = User::where('available', true)->where('hidden', false)->count();

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

        $text = null;
        if($available_users_count_before == 5 && !$available && $last_availability) {
            $text = "🧯 Distaccamento operativo per supporto";
        } else if($available_users_count_before == 2 && !$available && $last_availability) {
            $text = "⚠️ Distaccamento non operativo";
        } else if($available_users_count_before == 4 && $available && !$last_availability) {
            $text = "🚒 Distaccamento operativo con squadra completa";
        } else if($available_users_count_before == 1 && $available) {
            $text = "🧯 Distaccamento operativo per supporto";
        }
        TelegramBot::sendTeamMessage($text);

        Logger::log(
            "Disponibilità cambiata in ".($available ? "disponibile" : "non disponibile"),
            $user,
            $fromTelegram ? $user : null,
            $fromTelegram ? "telegram" : "web"
        );

        return [
            "updated_user_id" => $user->id,
            "updated_user_name" => $user->name
        ];
    }

    public static function updateAvailabilityManualMode(User|int $id, bool $manual_mode, bool $fromTelegram = false)
    {
        if(is_int($id)) {
            $user = User::find($id);
        } else {
            $user = $id;
        }
        $user->availability_manual_mode = $manual_mode;
        $user->save();

        Logger::log(
            ($manual_mode ? "Disattivazione" : "Attivazione")." programmazione oraria",
            $user,
            $fromTelegram ? $user : null,
            $fromTelegram ? "telegram" : "web"
        );
    }
}
