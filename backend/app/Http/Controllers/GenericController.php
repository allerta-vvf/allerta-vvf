<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class GenericController extends Controller
{
    /**
     * Returns the owner image
     * @unauthenticated
     */
    public function ownerImage()
    {
        return response()
            ->file(
                resource_path('images') . DIRECTORY_SEPARATOR . config("features.owner_image"),
                ['Cache-control' => 'max-age=2678400']
            );
    }

    /**
     * Returns a pong message
     */
    public function ping()
    {
        return response()->json([
            'message' => 'pong'
        ]);
    }

    /**
     * Execute scheduled tasks
     */
    public function executeCron(Request $request)
    {
        //Go to app/Console/Kernel.php to view schedules
        if(config('cron.external_cron_enabled') && $request->header('Cron') == config('cron.execution_code')) {
            Artisan::call('schedule:run');
            return response()->json([
                'message' => 'Cron executed'
            ]);
        } else {
            return response('Access Denied', 403);
        }
    }
}
