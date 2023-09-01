<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return response()->json(
            Log::join('users as changed_user', 'changed_user.id', '=', 'logs.changed_id')
              ->join('users as editor_user', 'editor_user.id', '=', 'logs.editor_id')
              ->select("logs.id", "logs.action", "logs.editor_id", "logs.changed_id", "logs.created_at", "changed_user.name as changed", "editor_user.name as editor")
              ->orderBy('created_at', 'desc')
              ->get()
        );
    }
}
