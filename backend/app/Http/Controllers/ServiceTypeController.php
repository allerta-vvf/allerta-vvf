<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\Logger;

class ServiceTypeController extends Controller
{
    /**
     * List all ServiceTypes.
     */
    public function index(Request $request)
    {
        if(!$request->user()->hasPermission("services-read")) abort(401);
        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        return response()->json(
            ServiceType::get()
        );
    }

    /**
     * Add a new ServiceType.
     */
    public function create(Request $request)
    {
        if(!$request->user()->hasPermission("services-create")) abort(401);
        $serviceType = new ServiceType();
        $serviceType->name = $request->name;
        $serviceType->save();

        Logger::log("Aggiunto tipo di intervento ($serviceType->name)");

        return response()->json(
            $serviceType
        );
    }
}
