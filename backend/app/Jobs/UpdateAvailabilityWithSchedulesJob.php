<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\ScheduleSlots;
use App\Models\User;

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

        User::whereIn("id", $scheduled_users)
            ->where([
                ["banned", "=", 0],
                ["availability_manual_mode", "=", 0]
            ])
            ->update(['available' => 1]);

        User::whereNotIn("id", $scheduled_users)
            ->where([
                ["banned", "=", 0],
                ["availability_manual_mode", "=", 0]
            ])
            ->update(['available' => 0]);
    }
}
