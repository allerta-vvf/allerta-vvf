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
            /**
             * @example 1
             */
            "id" => $this->id,
            /**
             * @example Nome
             */
            "name" => $this->name,
            /**
             * @example Cognome
             */
            "surname" => $this->surname,
            /**
             * @var string|null
             * @example Mos Eisley
             */
            "birthplace" => $this->birthplace,
            /**
             * @var string|null
             * @example AB
             */
            "birthplace_province" => $this->birthplace_province,
            /**
             * @var string|null
             */
            "ssn" => $this->ssn,
            /**
             * @var string|null
             */
            "address" => $this->address,
            /**
             * @var string|null
             * @example 12345
             */
            "address_zip_code" => $this->address_zip_code,
            /**
             * @var string|null
             * @example XL
             */
            "suit_size" => $this->suit_size,
            /**
             * @var string|null
             * @example 41
             */
            "boot_size" => $this->boot_size,
            "email" => $this->email,
            "email_verified_at" => $this->email_verified_at,
            /** @var string|null */
            "created_at" => $this->created_at,
            /** @var string|null */
            "updated_at" => $this->updated_at,
            "username" => $this->username,
            /**
             * @var string|null
             * @example null
             */
            "phone_number" => $this->phone_number,
            "available" => $this->available,
            "availability_manual_mode" => $this->availability_manual_mode,
            /**
             * @var string|null
             * @example 2024-04-04T12:53:49.000000Z
             */
            "last_availability_change" => $this->last_availability_change,
            /**
             * @var string|null
             * @example 2024-04-04T12:53:49.000000Z
             */
            "birthday" => $this->birthday,
            /**
             * @var string|null
             * @example 2024-04-04T12:53:49.000000Z
             */
            "course_date" => $this->course_date,
            "availability_minutes" => $this->availability_minutes,
            "chief" => $this->chief,
            "driver" => $this->driver,
            "services" => $this->services,
            "trainings" => $this->trainings,
            "last_access" => $this->last_access,
            "banned" => $this->banned,
            "hidden" => $this->hidden,
            /**
             * @var DrivingLicenseResource|null
             * @example {
             *   "doc_type": "TIPOLOGIAPATENTE",
             *   "doc_number": "NUMEROPATENTE",
             *   "expiration_date": "2024-04-15T20:17:20.000000Z",
             *   "scan_uuid": "49ad7215-721d-44ae-aa52-6a8fbdfd7058",
             *   "scan_url": "http://allertavvf.test/api/documents/driving_license/49ad7215-721d-44ae-aa52-6a8fbdfd7058?expires=1712842040&signature=fbd4244d7ec25e984d77d52f35ac57bd4c2b03274d71830ca473c8951a89562c"
             * }
             */
            "driving_license" => is_null($this->driving_license) ? null : DrivingLicenseResource::make($this->driving_license),
            /**
             * @example [
             *   {
             *     "doc_number": "68",
             *     "date": "2024-04-01T20:28:36.000000Z",
             *     "type": "Tipologia1_corso",
             *     "doc_url": "http://allertavvf.test/api/documents/training_course/2640d770-1a3e-47b2-ae87-0f02ea0e11e5?expires=1712845265&signature=cafebabdcb2e8427427431910d76da1e203c6f9cb4fd591a6ec3cbe7ee311e09"
             *   },
             *   {
             *     "doc_number": "67",
             *     "date": "2024-04-08T20:28:36.000000Z",
             *     "type": "Tipologia2_corso",
             *     "doc_url": null
             *   }
             * ]
             */
            "training_courses" => TrainingCoursesListResource::collection($this->training_courses),
            /**
             * @example [
             *   {
             *     "certifier": "Certificatore",
             *     "date": "2024-02-04T22:18:38.000000Z",
             *     "expiration_date": "2024-12-30T22:25:21.000000Z",
             *     "cert_url": "http://allertavvf.test/api/documents/medical_examination/d1a4ea10-fd4a-4e65-97c6-d3fee68e5397?expires=1712845265&signature=b3216f4f4d509ff5b189dd99d78ea4d54c5555445f3d3b3404cea16a285e395f"
             *   },
             *   {
             *     "certifier": "Certificatore",
             *     "date": "2024-04-01T20:26:50.000000Z",
             *     "expiration_date": "2024-04-29T20:27:15.000000Z",
             *     "cert_url": null
             *   }
             * ]
             */
            "medical_examinations" => MedicalExaminationsListResource::collection($this->medical_examinations),
        ];
    }
}
