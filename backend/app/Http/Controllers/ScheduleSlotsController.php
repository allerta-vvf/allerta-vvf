<?php

namespace App\Http\Controllers;

use App\Models\ScheduleSlots;
use Illuminate\Http\Request;

class ScheduleSlotsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return ScheduleSlots::select("day", "slot")
            ->where('user', $request->user()->id)
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        ScheduleSlots::where('user', $request->user()->id)->delete();

        $schedules = array_map(function ($schedule) {
            $schedule["user"] = auth()->id();
            return $schedule;
        }, $request->input('schedules'));

        return ScheduleSlots::insert($schedules);
    }
}
