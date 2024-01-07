<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Utils\Alerts;
use App\Utils\Logger;
use App\Utils\TelegramBot;
use App\Exceptions\AlertClosed;
use App\Exceptions\AlertResponseAlreadySet;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(!request()->user()->hasPermission("alerts-read")) abort(401);
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
        if(!$request->user()->hasPermission("alerts-create")) abort(401);
        $alert = Alerts::addAlert(
            $request->input('type', 'support'),
            $request->input('ignoreWarning', false)
        );

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
        if(!$request->user()->hasPermission("alerts-read")) abort(401);
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
        if(!$request->user()->hasPermission("alerts-update")) abort(401);
        $alert = Alert::find($id);
        $alert->notes = $request->input('notes', $alert->notes);
        $alert->closed = $request->input('closed', $alert->closed);
        $alert->updatedBy()->associate(auth()->user());
        $alert->save();

        TelegramBot::editSpecialMessage(
            $alert->id,
            "alert",
            "alert",
            Alerts::generateAlertTeamMessage($alert)
        );

        Logger::log(
            "Modifica informazioni allerta",
            auth()->user(),
            null,
            "web"
        );
    }

    /**
     * Set current user response to alert
     */
    public function setResponse(Request $request, $id)
    {
        if(!$request->user()->hasPermission("alerts-read")) abort(401);
        try {
            Alerts::updateAlertResponse($id, $request->input('response'));
        } catch(AlertClosed $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'La chiamata è stata chiusa.',
            ], 400);
        } catch(AlertResponseAlreadySet $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hai già risposto a questa chiamata.',
            ], 400);
        }
    }
}
