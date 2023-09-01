<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Place;
use App\Models\User;

class PlacesController extends Controller
{
    /**
     * Search place using OSM APIs.
     */
    public function search(Request $request)
    {
        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        $query = $request->input('q', null);
        if(!$query) abort(400);

        $query_hash = md5($query);
        $seconds = 60 * 60 * 24 * 30; // 30 days
        $result = Cache::remember('nominatim_'.$query_hash, $seconds, function () use ($query) {
            return Http::withUrlParameters([
                'place' => $query,
            ])->get('https://nominatim.openstreetmap.org/search?format=json&limit=6&q={place}')->object();
        });
        return response()->json($result);
    }

    public function show(Request $request, $id)
    {
        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        return response()->json(
            Place::find($id)
        );
    }
}
