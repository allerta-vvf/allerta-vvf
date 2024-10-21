<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MunicipalityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Not used for responses, but for the API documentation. (see PlaceResource.php)
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'postal_code' => $this->postal_code,
            'province' => [
                'id' => $this->province->id,
                'code' => $this->province->code,
                'name' => $this->province->name,
                'short_name' => $this->province->short_name,
                'region' => $this->province->region,
            ],
        ];
    }
}
