<?php

namespace App\Http\Resources\Finances;

use Illuminate\Http\Request;
use App\Http\Resources\Utils\DateTimeResource;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationBatchResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'uuid' => $this->uuid,
      'name' => $this->name,
      'description' => $this->description,
      'start_date' => $this->start_date->format('Y-m-d'),
      'end_date' => $this->end_date->format('Y-m-d'),
      'notes' => $this->notes,
      'status' => $this->status,
      'created_at' => DateTimeResource::make($this->created_at),
      'updated_at' => DateTimeResource::make($this->updated_at)
    ];
  }
}
