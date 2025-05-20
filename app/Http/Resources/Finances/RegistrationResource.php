<?php

namespace App\Http\Resources\Finances;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Academics\StudentResource;
use App\Http\Resources\Academics\StudentAddressResource;

class RegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'student_id' => $this->student_id,
            'student' => new StudentResource($this->whenLoaded('student')),
            'address_id' => $this->address_id,
            'address' => new StudentAddressResource($this->whenLoaded('address')),
            'student_category' => $this->student_category,
            'payment_method' => $this->payment_method,
            'program_type' => $this->program_type,
            'tutorial_service' => $this->tutorial_service,
            'semester' => $this->semester,
            'interested_spp' => $this->interested_spp,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
