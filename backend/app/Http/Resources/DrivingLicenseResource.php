<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DrivingLicenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(): array
    {
        return [
            'doc_type' => $this->doc_type,
            'doc_number' => $this->doc_number,
            'expiration_date' => $this->expiration_date,
            'scan_uuid' => $this->scan_uuid,
            'scan_url' => $this->scan_url,
        ];
    }
}
