<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'village' => $this->village,
            /** @var string */
            'created_at' => $this->created_at,
            /** @var string */
            'updated_at' => $this->updated_at,
            /** @var MunicipalityResource|null */
            'municipality' => $this->municipality,
        ];
    }
}
