<?php

namespace App\Http\Resources\Finances;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
  public function toArray($request): array
  {
    return [
      'uuid' => $this->uuid,
      'student' => $this->student->only(['id', 'name', 'nim']),
      'billing' => $this->billing->only(['uuid', 'billing_code']),
      'total_amount' => $this->total_amount,
      'due_date' => $this->due_date,
      'payment_status' => $this->payment_status,
      'payment_method' => $this->payment_method,
      'payment_type' => $this->payment_type,
      'note' => $this->note,
      'details' => $this->details->map(function ($item) {
        return [
          'item_name' => $item->item_name,
          'item_type' => $item->item_type,
          'amount' => $item->amount,
        ];
      }),
    ];
  }
}
