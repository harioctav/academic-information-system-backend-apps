<?php

namespace App\Http\Resources\Finances;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nim' => $this->student?->nim,
            'student_name' => $this->student?->name,
            'payment_method' => $this->payment_method,
            'bank_fee' => $this->bank_fee,
            'subscription_fee' => $this->subscription_fee,
            'subscription_code' => $this->subscription_code,
            'billing_code' => $this->billing_code,
            'payment_status' => $this->payment_status,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
