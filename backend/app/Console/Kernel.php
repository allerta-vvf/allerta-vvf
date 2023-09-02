<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Jobs\UpdateAvailabilityWithSchedulesJob;
use App\Jobs\NotifyUsersManualModeOn;
use App\Jobs\ResetAvailabilityMinutes;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->job(new UpdateAvailabilityWithSchedulesJob)
          ->everyThirtyMinutes()
          ->sentryMonitor();
        $schedule->job(new NotifyUsersManualModeOn)
          ->dailyAt('7:00')
          ->sentryMonitor();
        $schedule->job(new ResetAvailabilityMinutes)
          ->monthlyOn(1, '0:00')
          ->sentryMonitor();
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
