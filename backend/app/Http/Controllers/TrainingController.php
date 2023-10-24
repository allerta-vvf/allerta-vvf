<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\User;
use Illuminate\Http\Request;
use App\Utils\Logger;

class TrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        return response()->json(
            Training::join('users', 'users.id', '=', 'chief_id')
                ->select('trainings.*', 'users.name as chief')
                ->with('crew:name')
                ->orderBy('start', 'desc')
                ->get()
        );
    }

    /**
     * Get single Training
     */
    public function show(Request $request, $id)
    {
        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        return response()->json(
            Training::join('users', 'users.id', '=', 'chief_id')
                ->select('trainings.*', 'users.name as chief')
                ->with('crew:name')
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $training = Training::find($id);
        $usersToDecrement = $this->extractTrainingUsers($training);
        User::whereIn('id', $usersToDecrement)->decrement('trainings');
        $training->delete();
        Logger::log("Esercitazione eliminata");
    }
}
