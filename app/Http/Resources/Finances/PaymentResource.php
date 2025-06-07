<?php

namespace App\Http\Resources\Finances;

use App\Http\Resources\Utils\DateTimeResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Academics\StudentResource;

class PaymentResource extends JsonResource
{
  public function toArray($request): array
  {
    return [
      'uuid' => $this->uuid,
      'student' => new StudentResource($this->whenLoaded('student')),
      'billing' => new BillingResource($this->whenLoaded('billing')),
      'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
      'payment_method' => $this->payment_method,
      'payment_plan' => $this->payment_plan,
      'payment_date' => $this->payment_date,
      'amount_paid' => $this->amount_paid,
      'transfer_to' => $this->transfer_to,
      'proof_of_payment' => $this->proof_of_payment ? asset('storage/' . $this->proof_of_payment) : null,
      'payment_status' => $this->payment_status,
      'note' => $this->note,
      'created_at' => DateTimeResource::make($this->created_at),
    ];
  }
}
