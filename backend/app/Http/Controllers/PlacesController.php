<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Place;
use App\Models\User;
use App\Utils\HttpClient;

class PlacesController extends Controller
{
    /**
     * Search place using OSM APIs.
     */
    public function reverseSearch(Request $request)
    {
        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        $query = $request->input('q', null);
        if(!$query) abort(400);

        $query_hash = md5($query);
        $seconds = 60 * 60 * 24 * 30; // 30 days
        $result = Cache::remember('nominatim_'.$query_hash, $seconds, function () use ($query) {
            return HttpClient::defaultClient()->withUrlParameters([
                'place' => $query,
            ])->get('https://nominatim.openstreetmap.org/search?format=json&limit=5&q={place}')->object();
        });
        return response()->json($result);
    }

    /**
     * List all the regions of Italy.
     */
    public function italyListRegions()
    {
        $seconds = 60 * 60 * 24 * 365 * 10; // 10 years
        $result = Cache::remember('italy_regions', $seconds, function () {
            return HttpClient::defaultClient()->get('https://axqvoqvbfjpaamphztgd.functions.supabase.co/regioni')->object();
        });
        return response()->json($result);
    }

    /**
     * List all the provinces of a region of Italy.
     */
    public function italyListProvincesByRegion(Request $request, string $region_name)
    {
        $region_name = strtolower($region_name);
        $seconds = 60 * 60 * 24 * 365; // 1 year
        $result = Cache::remember('italy_provinces_'.$region_name, $seconds, function () use ($region_name) {
            return HttpClient::defaultClient()->get('https://axqvoqvbfjpaamphztgd.functions.supabase.co/province/'.$region_name)->object();
        });
        return response()->json($result);
    }

    /**
     * List all the municipalities of a province of Italy.
     */
    public function italyListMunicipalitiesByProvince(Request $request, string $province_name)
    {
        $province_name = strtolower($province_name);
        $seconds = 60 * 60 * 24 * 365; // 1 year
        $result = Cache::remember('italy_municipalities_'.$province_name, $seconds, function () use ($province_name) {
            return HttpClient::defaultClient()->get('https://axqvoqvbfjpaamphztgd.functions.supabase.co/comuni/provincia/'.$province_name)->object();
        });
        return response()->json($result);
    }

    /**
     * Return the place saved in DB with the given id.
     */
    public function show(Request $request, $id)
    {
        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        return response()->json(
            Place::where('id', $id)
                ->with('municipality', 'municipality.province')
                ->firstOrFail()
        );
    }
}
