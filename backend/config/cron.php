<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable external Cron
    |--------------------------------------------------------------------------
    |
    | If you enable this, you can use external Cron software like cron-job.org
    | to trigger the scheduler and run scheduled jobs.
    | For more information about running scheduled tasks, visit
    | https://laravel.com/docs/10.x/scheduling#running-the-scheduler
    |
    */

    'external_cron_enabled' => env('CRON_EXTERNAL_ENABLE', false),

    /*
    |--------------------------------------------------------------------------
    | Cron execution code
    |--------------------------------------------------------------------------
    |
    | Using this code you can prevent random users from running the scheduler
    | and ruining your scheduled jobs execution.
    | Remember to set this in your external Cron software, using the
    | "Cron" header.
    |
    */

    'execution_code' => env('CRON_EXEC_CODE', env('APP_KEY')),
];
