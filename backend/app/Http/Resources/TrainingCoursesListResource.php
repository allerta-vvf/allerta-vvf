<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TrainingCoursesListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(): array
    {
        return [
            "doc_number" => $this->doc_number,
            "date" => $this->date,
            "type" => $this->type,
            /** @var string|null */
            "doc_url" => $this->doc_url,
        ];
    }
}
