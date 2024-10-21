<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "code" => $this->code,
            "notes" => $this->notes,
            "start" => $this->start,
            "end" => $this->end,
            "added_by_id" => $this->added_by_id,
            "updated_by_id" => $this->updated_by_id,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "chief" => $this->chief,
            "type" => $this->type,
            "drivers" => ServiceOrTrainingUsersListResource::collection($this->drivers),
            "crew" => ServiceOrTrainingUsersListResource::collection($this->crew),
            "place" => PlaceCompleteResource::make($this->place),
        ];
    }
}
