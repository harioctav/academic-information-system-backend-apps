<?php

namespace App\Http\Resources\Finances;

use App\Enums\Finances\PaymentSystem;
use App\Enums\Finances\ProgramType;
use App\Enums\Finances\StudentCategory;
use Illuminate\Http\Request;
use App\Http\Resources\Utils\DateTimeResource;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Academics\StudentResource;
use App\Http\Resources\Finances\RegistrationBatchResource;

class RegistrationResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'uuid' => $this->uuid,
      'student_id' => $this->student_id,
      'student' => new StudentResource($this->whenLoaded('student')),
      'registration_batch_id' => $this->registration_batch_id,
      'registration_batch' => new RegistrationBatchResource($this->whenLoaded('registrationBatch')),
      'registration_number' => $this->registration_number,
      'shipping_address' => $this->shipping_address,
      'student_category' => StudentCategory::tryFrom($this->student_category)?->label(),
      'payment_system' => PaymentSystem::tryFrom($this->payment_system)?->label(),
      'program_type' => ProgramType::tryFrom($this->program_type)?->label(),
      'tutorial_service' => $this->tutorial_service,
      'semester' => $this->semester,
      'interested_spp' => $this->interested_spp,
      'created_at' => DateTimeResource::make($this->created_at),
      'updated_at' => DateTimeResource::make($this->updated_at),
    ];
  }
}
