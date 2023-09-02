<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\User;
use App\Models\AvailabilityMinutesArchive;

class ResetAvailabilityMinutes implements ShouldQueue
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
        //Get users so we can send notifications
        $users = User::select("id", "availability_minutes")
            ->where("hidden", false)
            ->get();

        //Reset availability minutes
        User::where("hidden", false)
            ->update(['availability_minutes' => 0]);

        //Archive availability minutes
        $year = date("Y");
        $month = date("m");
        foreach ($users as $user) {
            $row = new AvailabilityMinutesArchive();
            $row->availability_minutes = $user->availability_minutes;
            $row->year = $year;
            $row->month = $month;
            $row->user()->associate($user->id);
            $row->save();
        }
    }

    public function failed(\Exception $exception)
    {
        if (app()->bound('sentry')) {
            app('sentry')->captureException($exception);
        }
    }
}
