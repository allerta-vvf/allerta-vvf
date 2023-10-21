<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function execCmd(Request $request)
    {
        //Execute a job on the server
        $request->validate([
            'cmd' => 'required|string|max:1024',
        ]);

        $cmd = $request->input('cmd');

        return Artisan::call($cmd, json_decode($request->input('args', '{}'), true));
    }
}
