<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceOrTrainingUsersListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "name" => $this->name,
            "surname" => $this->surname,
            "pivot" => [
                "user_id" => $this->pivot->user_id
            ],
        ];
    }
}
