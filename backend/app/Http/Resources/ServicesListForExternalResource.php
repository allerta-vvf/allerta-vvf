<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServicesListForExternalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(): array
    {
        return [
            "code" => $this->code,
            "notes" => $this->notes,
            "start" => $this->start,
            "end" => $this->end,
            "created_at" => $this->created_at,
            "chief" => $this->chief,
            "type" => $this->type,
            "drivers" => ServiceOrTrainingUsersListResourceNoPivot::collection($this->drivers),
            "crew" => ServiceOrTrainingUsersListResourceNoPivot::collection($this->crew),
            "place" => PlaceResource::make($this->place),
        ];
    }
}
