<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServiceResource;
use App\Http\Resources\ServicesListResource;
use App\Http\Resources\ServicesListForExternalResource;
use App\Models\Place;
use App\Models\PlaceMunicipality;
use App\Models\PlaceProvince;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Utils\Logger;
use App\Utils\DBTricks;
use App\Utils\Helpers;

class ServiceController extends Controller
{
    /**
     * Show all Services
     */
    public function index(Request $request)
    {
        if (!$request->user()->hasPermission("services-read")) abort(401);
        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        $query = Service::join('users', 'users.id', '=', 'chief_id')
            ->join('services_types', 'services_types.id', '=', 'type_id')
            ->select(
                'services.*', DBTricks::nameSelect("chief", "users"),
                'services_types.name as type'
            )
            ->with('drivers:name,surname')
            ->with('crew:name,surname')
            ->with('place.municipality.province')
            ->orderBy('start', 'desc');
        if ($request->has('from')) {
            try {
                $from = Carbon::parse($request->input('from'));
                $query->whereDate('start', '>=', $from->toDateString());
            } catch (\Carbon\Exceptions\InvalidFormatException $e) {
            }
        }
        if ($request->has('to')) {
            try {
                $to = Carbon::parse($request->input('to'));
                $query->whereDate('start', '<=', $to->toDateString());
            } catch (\Carbon\Exceptions\InvalidFormatException $e) {
            }
        }

        // Handle search queries
        $searchQueries = $request->query('query');
        // Try parsing JSON
        if (is_string($searchQueries)) {
            $searchQueries = json_decode($searchQueries, true);
        }
        if ($searchQueries && is_array($searchQueries)) {
            foreach ($searchQueries as $searchQuery) {
                switch ($searchQuery['query']) {
                    case 'last':
                        //Last n services
                        $query->take($searchQuery['value']);
                        break;

                    case 'from':
                        //From date
                        try {
                            $from = Carbon::parse($searchQuery['value']);
                            $query->whereDate('start', '>=', $from->toDateString());
                        } catch (\Carbon\Exceptions\InvalidFormatException $e) {
                        }
                        break;

                    case 'to':
                        //To date
                        try {
                            $to = Carbon::parse($searchQuery['value']);
                            $query->whereDate('start', '<=', $to->toDateString());
                        } catch (\Carbon\Exceptions\InvalidFormatException $e) {
                        }
                        break;

                    case 'type':
                        //Type
                        $query->where('services_types.name', 'like', '%' . $searchQuery['value'] . '%');
                        break;
                }
            }
        }

        $result = $query->get();
        foreach ($result as $service) {
            if($service->place->municipality) {
                $m = $service->place->municipality;
                unset(
                    $m->cadastral_code, $m->email, $m->fax, $m->latitude, $m->longitude,
                    $m->phone, $m->pec, $m->prefix, $m->foreign_name, $m->province_id
                );
            }

            $p = $service->place;
            unset($p->lat, $p->lon, $p->place_id, $p->osm_id, $p->osm_type, $p->licence, $p->addresstype, $p->country, $p->country_code, $p->display_name, $p->road, $p->house_number, $p->postcode, $p->state, $p->suburb, $p->city, $p->municipality_id);
        }
        if ($request->has('external')) {
            return ServicesListForExternalResource::collection($result);
        }
        return ServicesListResource::collection($result);
    }

    /**
     * Get single Service
     */
    public function show(Request $request, $id)
    {
        if (!$request->user()->hasPermission("services-read")) abort(401);
        User::where('id', $request->user()->id)->update(['last_access' => now()]);

        return ServiceResource::make(
            Service::join('users', 'users.id', '=', 'chief_id')
                ->join('services_types', 'services_types.id', '=', 'type_id')
                ->select('services.*', DBTricks::nameSelect("chief", "users"), 'services_types.name as type')
                ->with('drivers:name,surname')
                ->with('crew:name,surname')
                ->with('place.municipality.province')
                ->find($id)
        );
    }

    private function extractServiceUsers($service)
    {
        $usersList = [$service->chief_id];
        foreach ($service->drivers as $driver) {
            $usersList[] = $driver->id;
        }
        foreach ($service->crew as $crew) {
            $usersList[] = $crew->id;
        }
        return array_unique($usersList);
    }

    /**
     * Add or update Service.
     */
    public function createOrUpdate(Request $request)
    {
        $adding = !isset($request->id) || is_null($request->id);

        if (!$adding && !$request->user()->hasPermission("services-update")) abort(401);
        if ($adding && !$request->user()->hasPermission("services-create")) abort(401);

        $service = $adding ? new Service() : Service::where("id", $request->id)->with('drivers')->with('crew')->first();

        if (is_null($service)) abort(404);

        if ($adding) {
            //Check if code already exists
            if (Service::where('code', $request->code)->exists()) {
                return response()->json([
                    'message' => "Il codice inserito è già stato utilizzato in un altro intervento"
                ], 402);
            }
        } else {
            $usersToDecrement = $this->extractServiceUsers($service);
            User::whereIn('id', $usersToDecrement)->decrement('services');

            $service->drivers()->detach();
            $service->crew()->detach();
            $service->save();
        }

        $is_map_picker = Helpers::get_option('service_place_selection_use_map_picker', false);

        if ($is_map_picker) {
            //Find Place by lat lon
            $place = Place::where('lat', $request->place["lat"])->where('lon', $request->place["lon"])->first();
            if (!$place) {
                $place = new Place();
                $place->lat = $request->place["lat"];
                $place->lon = $request->place["lon"];

                $response = Http::withUrlParameters([
                    'lat' => $request->place["lat"],
                    'lon' => $request->place["lon"],
                ])->get('https://nominatim.openstreetmap.org/reverse?format=json&lat={lat}&lon={lon}');
                if (!$response->ok()) abort(500);

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

                $place->save();
            }
        } else {
            if (!$adding) {
                //Delete old place
                $place = $service->place;
                $service->place()->dissociate();
                $service->save();
                $place->delete();
            }

            $place = new Place();
            $place->name = $request->place["address"];

            //Check if municipality exists
            $municipality = PlaceMunicipality::where('code', $request->place["municipalityCode"])->first();
            if (!$municipality) {
                //Check if province exists
                $province = PlaceProvince::where('code', $request->place["provinceCode"])->first();
                if (!$province) {
                    $provinces = Cache::remember('italy_provinces_all', 60 * 60 * 24 * 365, function () {
                        return Http::get('https://axqvoqvbfjpaamphztgd.functions.supabase.co/province/')->object();
                    });

                    //Find province
                    foreach ($provinces as $p) {
                        if ($p->codice == $request->place["provinceCode"]) {
                            $province = new PlaceProvince();
                            $province->code = $p->codice;
                            $province->name = $p->nome;
                            $province->short_name = $p->sigla;
                            $province->region = $p->regione;
                            $province->save();
                            break;
                        }
                    }
                    if (!$province) {
                        abort(400);
                    }
                }

                $province_name = $province->name;
                $municipalities = Cache::remember('italy_municipalities_' . $province_name, 60 * 60 * 24 * 365, function () use ($province_name) {
                    return Http::get('https://axqvoqvbfjpaamphztgd.functions.supabase.co/comuni/provincia/' . $province_name)->object();
                });

                //Find municipality
                foreach ($municipalities as $m) {
                    if ($m->codice == $request->place["municipalityCode"]) {
                        $municipality = new PlaceMunicipality();
                        $municipality->code = $m->codice;
                        $municipality->name = $m->nome;
                        $municipality->foreign_name = $m->nomeStraniero;
                        $municipality->cadastral_code = $m->codiceCatastale;
                        $municipality->postal_code = $m->cap;
                        $municipality->prefix = $m->prefisso;
                        $municipality->email = $m->email;
                        $municipality->pec = $m->pec;
                        $municipality->phone = $m->telefono;
                        $municipality->fax = $m->fax;
                        $municipality->latitude = $m->coordinate->lat;
                        $municipality->longitude = $m->coordinate->lng;
                        $municipality->province()->associate($province);
                        $municipality->save();
                        break;
                    }
                }
                if (!$municipality) {
                    abort(400);
                }
            }
            $place->municipality()->associate($municipality);

            $place->save();
        }

        $service->code = $request->code;
        $service->chief()->associate($request->chief);
        $service->type()->associate($request->type);
        $service->notes = $request->notes;
        $service->start = $request->start / 1000;
        $service->end = $request->end / 1000;
        $service->place()->associate($place);
        $service->addedBy()->associate($request->user());
        $service->updatedBy()->associate($request->user());
        $service->save();

        $service->drivers()->attach(array_unique($request->drivers));
        $service->crew()->attach(array_unique($request->crew));
        $service->save();

        $usersToIncrement = array_unique(array_merge(
            [$request->chief],
            $request->drivers,
            $request->crew
        ));
        User::whereIn('id', $usersToIncrement)->increment('services');

        Logger::log($adding ? "Intervento aggiunto" : "Intervento modificato");

        return response()->noContent();
    }

    /**
     * Delete Service
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->user()->hasPermission("services-delete")) abort(401);
        $service = Service::find($id);
        $usersToDecrement = $this->extractServiceUsers($service);
        User::whereIn('id', $usersToDecrement)->decrement('services');
        $service->delete();
        Logger::log("Intervento eliminato");

        return response()->noContent();
    }
}
