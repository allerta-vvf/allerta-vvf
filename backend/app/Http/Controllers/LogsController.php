<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\User;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        $query = Log::join('users as changed_user', 'changed_user.id', '=', 'logs.changed_id')
            ->join('users as editor_user', 'editor_user.id', '=', 'logs.editor_id')
            ->orderBy('created_at', 'desc');
        
        $selectedCols = [
            "logs.id", "logs.action", "logs.editor_id", "logs.changed_id", "logs.created_at", "logs.source_type",
            "changed_user.name as changed", "editor_user.name as editor", "editor_user.hidden as editor_hidden"
        ];
        
        if($request->user()->hasPermission("logs-limited-read")) {
            $query = $query->where(function ($query) {
                $query->where('editor_user.hidden', false)
                      ->orWhere('editor_user.id', auth()->user()->id);
            });
        } else if($request->user()->hasPermission("logs-read")) {
            $selectedCols = array_merge($selectedCols, ["logs.ip", "logs.user_agent"]);
        } else {
            abort(401);
        }

        return response()->json(
            $query->select($selectedCols)->get()
        );
    }
}
