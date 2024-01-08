<?php

namespace App\Http\Controllers;

use App\Models\TrainingCourseType;
use App\Models\User;
use Illuminate\Http\Request;
use App\Utils\Logger;

class TrainingCourseTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if(!$request->user()->hasPermission("users-read") && !$request->user()->hasPermission("user-read")) abort(401);
        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        return response()->json(
            TrainingCourseType::get()
        );
    }

    /**
     * Add a new TrainingCourseType.
     */
    public function create(Request $request)
    {
        if(!$request->user()->hasPermission("users-add-training-course") && !$request->user()->hasPermission("user-add-training-course")) abort(401);
        $trainingCourseType = new TrainingCourseType();
        $trainingCourseType->name = $request->name;
        $trainingCourseType->save();

        Logger::log("Aggiunto tipo di corso di formazione ($trainingCourseType->name)");

        return response()->json(
            $trainingCourseType
        );
    }
}
