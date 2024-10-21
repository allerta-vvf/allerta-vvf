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
            /**
             * @example 1
             */
            "id" => $this->id,
            /**
             * @var boolean
             * @example true
             */
            "chief" => $this->chief,
            /**
             * @example Nome
             */
            "name" => $this->name,
            /**
             * @example Cognome
             */
            "surname" => $this->surname,
            /**
             * @var boolean
             * @example true
             */
            "available" => $this->available,
            /**
             * @var boolean
             * @example true
             */
            "driver" => $this->driver,
            /**
             * @var int
             * @example 1
             */
            "services" => $this->services,
            /**
             * @var int
             * @example 4642
             */
            "availability_minutes" => $this->availability_minutes,
            /**
             * @var string|null
             * @example null
             */
            "phone_number" => $this->phone_number,
            /**
             * @var boolean
             * @example false
             */
            "online" => $this->online
        ];
    }
}
