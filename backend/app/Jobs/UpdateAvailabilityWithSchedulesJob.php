<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\ScheduleSlots;
use App\Models\User;
use App\Models\TelegramBotNotifications;

use DefStudio\Telegraph\Facades\Telegraph;

class UpdateAvailabilityWithSchedulesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //Days starts from 0 in frontend
        $curr_day = now()->dayOfWeek-1;
        //There are 48 slots of 30 minutes, starting from 0 (00:00-00:30) to 47 (23:30-00:00)
        $curr_slot = now()->hour * 2 + (now()->minute >= 30);

        $scheduled_users = ScheduleSlots::where([
            ["day", "=", $curr_day],
            ["slot", "=", $curr_slot]
        ])->pluck("user");

        $available_users_count_before = User::where('available', true)->where('hidden', false)->count();

        User::whereIn("id", $scheduled_users)
            ->where([
                ["banned", "=", 0],
                ["availability_manual_mode", "=", 0]
            ])
            ->update(['available' => 1]);

        $not_available_users = User::whereNotIn("id", $scheduled_users)
            ->where([
                ["banned", "=", 0],
                ["availability_manual_mode", "=", 0]
            ])
            ->get();
        foreach($not_available_users as $user) {
            $last_availability_change = $user->last_availability_change;
            $new_last_availability_change = now();

            if(!is_null($last_availability_change)) {
                $diff = $new_last_availability_change->diffInMinutes($last_availability_change);
                if($diff > 0) $user->availability_minutes += $diff;
            }

            $user->available = 0;
            $user->last_availability_change = $new_last_availability_change;
            $user->save();
        }

        $available_users_count_after = User::where('available', true)->where('hidden', false)->count();

        $text = null;
        if($available_users_count_before >= 2 && $available_users_count_after < 2) {
            $text = "⚠️ Distaccamento non operativo";
        } else if(($available_users_count_before < 2 || $available_users_count_before >= 5) && $available_users_count_after >= 2 && $available_users_count_after < 5) {
            $text = "🧯 Distaccamento operativo per supporto";
        } else if($available_users_count_before < 5 && $available_users_count_after >= 5) {
            $text = "🚒 Distaccamento operativo con squadra completa";
        }
        if(!is_null($text)) {
            //Find message hash
            $hash = md5($text);
            
            $chat_ids = TelegramBotNotifications::where("type_team_state", true)
              ->whereNot("last_message_hash", $hash)
              ->get()->pluck('chat_id')->toArray();
            
            foreach ($chat_ids as $chat_id) {
                $chat = Telegraph::chat($chat_id);
                $chat->message($text)->send();
                TelegramBotNotifications::where("chat_id", $chat_id)
                  ->update(["last_message_hash" => $hash]);
            }
        }
    }
}
