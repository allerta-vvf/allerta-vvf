<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceOrTrainingUsersListResourceNoPivot extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(): array
    {
        return [
            "name" => $this->name,
            "surname" => $this->surname,
        ];
    }
}
