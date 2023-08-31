<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(
            Service::join('users', 'users.id', '=', 'chief_id')
                ->join('services_types', 'services_types.id', '=', 'type_id')
                ->select('services.*', 'users.name as chief', 'services_types.name as type')
                ->with('drivers:name')
                ->with('crew:name')
                ->with('place')
                ->get()
        );
    }

    /**
     * Add a new Service.
     */
    public function create(Request $request)
    {
        //Find Place by lat lon
        $place = Place::where('lat', $request->lat)->where('lon', $request->lon)->first();
        if(!$place) {
            $place = new Place();
            $place->lat = $request->lat;
            $place->lon = $request->lon;

            $response = Http::withUrlParameters([
                'lat' => $request->lat,
                'lon' => $request->lon,
            ])->get('https://nominatim.openstreetmap.org/reverse?format=json&lat={lat}&lon={lon}');
            if(!$response->ok()) abort(500);

            $place->place_id = isset($response["place_id"]) ? $response["place_id"] : null;
            $place->osm_id = isset($response["osm_id"]) ? $response["osm_id"] : null;
            $place->osm_type = isset($response["osm_type"]) ? $response["osm_type"] : null;
            $place->licence = isset($response["licence"]) ? $response["licence"] : null;
            $place->addresstype = isset($response["addresstype"]) ? $response["addresstype"] : null;
            $place->country = isset($response["address"]["country"]) ? $response["address"]["country"] : null;
            $place->country_code = isset($response["address"]["country_code"]) ? $response["address"]["country_code"] : null;
            $place->name = isset($response["name"]) ? $response["name"] : null;
            $place->display_name = isset($response["display_name"]) ? $response["display_name"] : null;
            $place->road = isset($response["address"]["road"]) ? $response["address"]["road"] : null;
            $place->house_number = isset($response["address"]["house_number"]) ? $response["address"]["house_number"] : null;
            $place->postcode = isset($response["address"]["postcode"]) ? $response["address"]["postcode"] : null;
            $place->state = isset($response["address"]["state"]) ? $response["address"]["state"] : null;
            $place->village = isset($response["address"]["village"]) ? $response["address"]["village"] : null;
            $place->suburb = isset($response["address"]["suburb"]) ? $response["address"]["suburb"] : null;
            $place->city = isset($response["address"]["city"]) ? $response["address"]["city"] : null;
            $place->municipality = isset($response["address"]["municipality"]) ? $response["address"]["municipality"] : null;

            $place->save();
        }

        $service = new Service();
        $service->code = $request->code;
        $service->chief()->associate($request->chief);
        $service->type()->associate($request->type);
        $service->notes = $request->notes;
        $service->start = $request->start/1000; //TODO: fix client-side
        $service->end = $request->end/1000;
        $service->place()->associate($place);
        $service->addedBy()->associate($request->user());
        $service->updatedBy()->associate($request->user());
        $service->save();

        $service->drivers()->attach([3]);
        $service->crew()->attach([4, 5, 6]);
        $service->save();
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        //
    }
}
