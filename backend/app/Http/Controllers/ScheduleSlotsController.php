<?php

namespace App\Http\Controllers;

use App\Models\ScheduleSlots;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\Logger;

class ScheduleSlotsController extends Controller
{
    /**
     * Return the schedule slots of the user
     */
    public function index(Request $request)
    {
        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        return ScheduleSlots::select("day", "slot")
            ->where('user', $request->user()->id)
            ->get();
    }

    /**
     * Update the schedule slots of the user
     */
    public function store(Request $request)
    {
        ScheduleSlots::where('user', $request->user()->id)->delete();

        $schedules = array_map(function ($schedule) {
            $schedule["user"] = auth()->id();
            return $schedule;
        }, $request->input('schedules'));

        Logger::log("Aggiornata disponibilità oraria");

        return ScheduleSlots::insert($schedules);
    }
}
