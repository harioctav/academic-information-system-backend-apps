<?php

namespace App\Http\Resources\Finances;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Academics\StudentResource;
use App\Http\Resources\Finances\RegistrationBatchResource;

class RegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'student_id' => $this->student_id,
            'student' => new StudentResource($this->whenLoaded('student')),
            'registration_batch_id' => $this->registration_batch_id,
            'registration_batch' => new RegistrationBatchResource($this->whenLoaded('registrationBatch')),
            'registration_number' => $this->registration_number,
            'shipping_address' => $this->shipping_address,
            'student_category' => $this->student_category,
            'payment_system' => $this->payment_system,
            'program_type' => $this->program_type,
            'tutorial_service' => $this->tutorial_service,
            'semester' => $this->semester,
            'interested_spp' => $this->interested_spp,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
