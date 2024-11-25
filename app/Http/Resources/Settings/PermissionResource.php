<?php

namespace App\Http\Resources\Settings;

use App\Http\Resources\Utils\DateTimeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
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
      'name' => $this->name,
      'guard_name' => $this->guard_name,
      'permissionCategory' => PermissionCategoryResource::make($this->permissionCategory),
      'created_at' => DateTimeResource::make($this->created_at),
      'updated_at' => DateTimeResource::make($this->updated_at),
    ];
  }
}
