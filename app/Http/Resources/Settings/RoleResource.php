<?php

namespace App\Http\Resources\Settings;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'name' => UserRole::from($this->name)->label(),
      'permissions' => $this->permissions->pluck('name')
    ];
  }
}
