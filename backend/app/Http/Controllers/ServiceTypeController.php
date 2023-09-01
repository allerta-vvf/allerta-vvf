<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use Illuminate\Http\Request;
use App\Utils\Logger;

class ServiceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(
            ServiceType::get()
        );
    }

    /**
     * Add a new ServiceType.
     */
    public function create(Request $request)
    {
        $serviceType = new ServiceType();
        $serviceType->name = $request->name;
        $serviceType->save();

        Logger::log("Aggiunto tipo di intervento ($serviceType->name)");

        return response()->json(
            $serviceType
        );
    }
}
