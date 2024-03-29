<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Utils\Logger;
use App\Utils\DBTricks;

class TrainingController extends Controller
{
    /**
     * List all Trainings.
     */
    public function index(Request $request)
    {
        if(!$request->user()->hasPermission("trainings-read")) abort(401);
        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        $query = Training::join('users', 'users.id', '=', 'chief_id')
            ->select('trainings.*', DBTricks::nameSelect("chief", "users"))
            ->with('crew:name,surname')
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

    /**
     * Get single Training
     */
    public function show(Request $request, $id)
    {
        if(!$request->user()->hasPermission("trainings-read")) abort(401);
        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        return response()->json(
            Training::join('users', 'users.id', '=', 'chief_id')
                ->select('trainings.*', DBTricks::nameSelect("chief", "users"))
                ->with('crew:name,surname')
                ->find($id)
        );
    }

    private function extractTrainingUsers($training)
    {
        $usersList = [$training->chief_id];
        foreach($training->crew as $crew) {
            $usersList[] = $crew->id;
        }
        return array_unique($usersList);
    }

    /**
     * Add or update Training.
     */
    public function createOrUpdate(Request $request)
    {
        $adding = !isset($request->id) || is_null($request->id);

        if(!$adding && !$request->user()->hasPermission("trainings-update")) abort(401);
        if($adding && !$request->user()->hasPermission("trainings-create")) abort(401);

        $training = $adding ? new Training() : Training::where("id",$request->id)->with('crew')->first();

        if(is_null($training)) abort(404);

        if(!$adding) {
            $usersToDecrement = $this->extractTrainingUsers($training);
            User::whereIn('id', $usersToDecrement)->decrement('trainings');

            $training->crew()->detach();
            $training->save();
        }

        $training->name = $request->name;
        $training->chief()->associate($request->chief);
        $training->notes = $request->notes;
        $training->start = $request->start/1000;
        $training->end = $request->end/1000;
        $training->place = $request->place;
        $training->addedBy()->associate($request->user());
        $training->updatedBy()->associate($request->user());
        $training->save();

        $training->crew()->attach(array_unique($request->crew));
        $training->save();

        $usersToIncrement = array_unique(array_merge(
            [$request->chief],
            $request->crew
        ));
        User::whereIn('id', $usersToIncrement)->increment('trainings');

        Logger::log($adding ? "Esercitazione aggiunta" : "Esercitazione modificata");
    }

    /**
     * Delete Training
     */
    public function destroy(Request $request, $id)
    {
        if(!$request->user()->hasPermission("trainings-delete")) abort(401);
        $training = Training::find($id);
        $usersToDecrement = $this->extractTrainingUsers($training);
        User::whereIn('id', $usersToDecrement)->decrement('trainings');
        $training->delete();
        Logger::log("Esercitazione eliminata");
    }
}
