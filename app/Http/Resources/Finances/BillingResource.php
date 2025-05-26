<?php

namespace App\Http\Resources\Finances;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Http\Resources\Academics\StudentResource;
use App\Http\Resources\Finances\RegistrationResource;

class BillingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'student_id' => $this->student_id,
            'student' => new StudentResource($this->whenLoaded('student')),
            'registration_id' => $this->registration_id,
            'registration' => new RegistrationResource($this->whenLoaded('registration')),
            'registration_period' => $this->registration_period,
            'billing_code' => $this->billing_code,
            'billing_status' => $this->billing_status,
            'bank_fee' => $this->bank_fee,
            'salut_member_fee' => $this->salut_member_fee,
            'semester_fee' => $this->semester_fee,
            'total_fee' => $this->total_fee,
            'settlement_status' => $this->settlement_status,
            'settlement_date' => $this->settlement_date,
            'note' => $this->note,
            'created_at' => $this->created_at,
        ];
    }
}
