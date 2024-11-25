<?php

namespace App\Http\Resources\Locations;

use App\Http\Resources\Utils\DateTimeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VillageResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'uuid' => $this->uuid,
      'code' => $this->code,
      'name' => $this->name,
      'full_code' => $this->full_code,
      'pos_code' => $this->pos_code,
      'district' => DistrictResource::make($this->district),
      'created_at' => DateTimeResource::make($this->created_at),
      'updated_at' => DateTimeResource::make($this->updated_at),
    ];
  }
}
