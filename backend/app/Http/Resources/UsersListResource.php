<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsersListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "chief" => $this->chief,
            "name" => $this->name,
            "surname" => $this->surname,
            "available" => $this->available,
            "driver" => $this->driver,
            "services" => $this->services,
            "availability_minutes" => $this->availability_minutes,
            "phone_number" => $this->phone_number,
            "online" => false
        ];
    }
}
