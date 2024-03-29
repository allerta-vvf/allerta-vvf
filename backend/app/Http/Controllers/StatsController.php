<?php

namespace App\Http\Controllers;

use App\Models\Service;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StatsController extends Controller
{
    /**
     * Get all services with all data, for usage in statistics page
     */
    public function services(Request $request)
    {
        if(!$request->user()->hasPermission("services-read")) abort(401);
        $query = Service::select('id','code','chief_id','type_id','place_id','notes','start','end','added_by_id','created_at')
            ->with('place')
            ->with('drivers:id')
            ->with('crew:id')
            ->orderBy('start', 'desc');
        if($request->has('from')) {
            try {
                $from = Carbon::parse($request->input('from'));
                $query->whereDate('start', '>=', $from->toDateString());
            } catch (\Carbon\Exceptions\InvalidFormatException $e) { }
        }
        if($request->has('to')) {
            try {
                $to = Carbon::parse($request->input('to'));
                $query->whereDate('start', '<=', $to->toDateString());
            } catch (\Carbon\Exceptions\InvalidFormatException $e) { }
        }
        return response()->json(
            $query->get()
        );
    }
}
