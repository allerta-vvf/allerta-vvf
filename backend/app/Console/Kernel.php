<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Jobs\NotifyUsersManualModeOnJob;
use App\Jobs\RemoveOldIpAddressesFromLogsJob;
use App\Jobs\ResetAvailabilityMinutesJob;
use App\Jobs\UpdateAvailabilityWithSchedulesJob;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
      $schedule->job(new NotifyUsersManualModeOnJob)
        ->dailyAt('7:00')
        ->sentryMonitor('notify-users-manual-mode-on');
      $schedule->job(new RemoveOldIpAddressesFromLogsJob)
        ->dailyAt('0:30')
        ->sentryMonitor('remove-old-ip-addresses-from-logs');
      $schedule->job(new ResetAvailabilityMinutesJob)
        ->monthlyOn(1, '0:00')
        ->sentryMonitor('reset-availability-minutes');
      $schedule->job(new UpdateAvailabilityWithSchedulesJob)
        ->everyThirtyMinutes()
        ->sentryMonitor('update-availability-with-schedules');
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
