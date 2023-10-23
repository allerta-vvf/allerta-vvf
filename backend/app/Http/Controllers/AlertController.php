<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\AlertCrew;
use Illuminate\Http\Request;

use App\Models\User;

class AlertController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(
            request()->query('full', false) ?
                Alert::with(['crew.user' => function($query) {
                    $query->select(['id', 'name', 'username', 'chief', 'driver']);
                }])
                    ->with('addedBy:name')
                    ->with('updatedBy:name')
                    ->where('closed', false)
                    ->orderBy('created_at', 'desc')
                    ->get()
                :
                Alert::where('closed', false)
                    ->orderBy('created_at', 'desc')
                    ->get()
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $type = $request->input('type', 'support');
        $ignoreWarning = $request->input('ignoreWarning', false);

        //Count users when not hidden and available
        $count = User::where('hidden', false)->where('available', true)->count();

        if($count == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nessun utente disponibile.',
                'ignorable' => false,
            ], 400);
        }

        //Check if there is at least one chief available (not hidden)
        $chiefCount = User::where([
            ['hidden', '=', false],
            ['available', '=', true],
            ['chief', '=', true]
        ])->count();
        if($chiefCount == 0 && !$ignoreWarning) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nessun caposquadra disponibile. Sei sicuro di voler proseguire?',
                'ignorable' => true,
            ], 400);
        }

        //Check if there is at least one driver available (not hidden)
        $driverCount = User::where([
            ['hidden', '=', false],
            ['available', '=', true],
            ['driver', '=', true]
        ])->count();
        if($driverCount == 0 && !$ignoreWarning) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nessun autista disponibile. Sei sicuro di voler proseguire?',
                'ignorable' => true,
            ], 400);
        }

        //Select call list (everyone not hidden and available)
        $users = User::where('hidden', false)->where('available', true)->get();
        if(count($users) < 5 && $type = "full") $type = "support";
        
        //Create alert
        $alert = new Alert;
        $alert->type = $type;
        $alert->addedBy()->associate(auth()->user());
        $alert->updatedBy()->associate(auth()->user());
        $alert->save();

        //Create alert crew
        $alertCrewIds = [];
        foreach($users as $user) {
            $alertCrew = new AlertCrew();
            $alertCrew->user_id = $user->id;
            $alertCrew->save();

            $alertCrewIds[] = $alertCrew->id;
        }
        $alert->crew()->attach($alertCrewIds);
        $alert->save();

        //Return response
        return response()->json([
            'status' => 'success',
            'alert' => $alert,
        ], 200);
    }

    /**
     * Get single Alert
     */
    public function show(Request $request, $id)
    {
        return response()->json(
            Alert::where('id', $id)
                ->with(['crew.user' => function($query) {
                    $query->select(['id', 'name', 'username', 'chief', 'driver']);
                }])
                ->with('addedBy:name')
                ->with('updatedBy:name')
                ->first()
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Alert $Alert)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //TODO: improve permissions and roles
        if(!$request->user()->hasPermission("users-read")) abort(401);
        $alert = Alert::find($id);
        $alert->notes = $request->input('notes', $alert->notes);
        $alert->closed = $request->input('closed', $alert->closed);
        $alert->updatedBy()->associate(auth()->user());
        $alert->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Alert $Alert)
    {
        //
    }
}
