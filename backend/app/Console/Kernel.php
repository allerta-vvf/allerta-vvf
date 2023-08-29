<?php

namespace App\Console;

use App\Jobs\NotifyUsersManualModeOn;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\UpdateAvailabilityWithSchedulesJob;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new UpdateAvailabilityWithSchedulesJob)->everyThirtyMinutes();
        $schedule->job(new NotifyUsersManualModeOn)->dailyAt('7:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
