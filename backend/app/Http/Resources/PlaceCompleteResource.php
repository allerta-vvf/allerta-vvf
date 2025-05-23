<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlaceCompleteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name !== "" ? $this->name : $this["display_name"],
            'village' => $this->village,
            /** @var string */
            'created_at' => $this->created_at,
            /** @var string */
            'updated_at' => $this->updated_at,
            /** @var MunicipalityResource|null */
            'municipality' => $this->municipality,

            // OSM data, if selected using map selector

            /** @var number|null */
            'lat' => $this->lat,
            /** @var number|null */
            'lon' => $this->lon,
            /** @var number */
            'place_id' => $this->place_id,
            /** @var number|null */
            'osm_id' => $this->osm_id,
            /** @var string|null */
            'osm_type' => $this->osm_type,
            /** @var string|null */
            'licence' => $this->licence,
            /** @var string|null */
            'addresstype' => $this->addresstype,
            /** @var string|null */
            'country' => $this->country,
            /** @var string|null */
            'country_code' => $this->country_code,
            /** @var string|null */
            'road' => $this->road,
            /** @var string|null */
            'house_number' => $this->house_number,
            /** @var string|null */
            'postcode' => $this->postcode,
            /** @var string|null */
            'state' => $this->state,
            /** @var string|null */
            'suburb' => $this->suburb,
            /** @var string|null */
            'city' => $this->city,
        ];
    }
}
