<?php

namespace App\Http\Resources\Finances;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'billing_code' => $this->billing_code,
            'student' => $this->student,
            'bank_fee' => $this->bank_fee,
            'salut_member_fee' => $this->salut_member_fee,
            'semester_fee' => $this->semester_fee,
            'total_fee' => $this->total_fee,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'note' => $this->note,
            'created_at' => $this->created_at,
        ];
    }
}
