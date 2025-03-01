<?php

namespace App\Http\Resources\Settings;

use App\Enums\GeneralConstant;
use App\Http\Resources\Utils\DateTimeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
      'email' => $this->email,
      'status' => $this->status,
      'phone' => $this->phone,
      'photo_profile_path' => $this->photo_profile_path,
      'photo_url' => $this->photo_url,
      'last_activity' => DateTimeResource::make($this->last_activity),
      'roles' => RoleResource::collection($this->roles),
      'created_at' => DateTimeResource::make($this->created_at),
      'updated_at' => DateTimeResource::make($this->updated_at),
    ];
  }
}
