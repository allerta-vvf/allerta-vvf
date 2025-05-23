<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MedicalExaminationsListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(): array
    {
        return [
            "certifier" => $this->certifier,
            "date" => $this->date,
            "expiration_date" => $this->expiration_date,
            /** @var string|null */
            "cert_url" => $this->cert_url,
        ];
    }
}
