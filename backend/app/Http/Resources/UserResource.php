<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            "name" => $this->name,
            "surname" => $this->surname,
            "birthplace" => $this->birthplace,
            "birthplace_province" => $this->birthplace_province,
            "ssn" => $this->ssn,
            "address" => $this->address,
            "address_zip_code" => $this->address_zip_code,
            "suit_size" => $this->suit_size,
            "boot_size" => $this->boot_size,
            "email" => $this->email,
            "email_verified_at" => $this->email_verified_at,
            /** @var string|null */
            "created_at" => $this->created_at,
            /** @var string|null */
            "updated_at" => $this->updated_at,
            "username" => $this->username,
            "phone_number" => $this->phone_number,
            "available" => $this->available,
            "availability_manual_mode" => $this->availability_manual_mode,
            "last_availability_change" => $this->last_availability_change,
            "birthday" => $this->birthday,
            "course_date" => $this->course_date,
            "availability_minutes" => $this->availability_minutes,
            "chief" => $this->chief,
            "driver" => $this->driver,
            "services" => $this->services,
            "trainings" => $this->trainings,
            "last_access" => $this->last_access,
            "banned" => $this->banned,
            "hidden" => $this->hidden,
            /** @var DrivingLicenseResource|null */
            "driving_license" => is_null($this->driving_license) ? null : DrivingLicenseResource::make($this->driving_license),
            "training_courses" => TrainingCoursesListResource::collection($this->training_courses),
            "medical_examinations" => MedicalExaminationsListResource::collection($this->medical_examinations),
        ];
    }
}
