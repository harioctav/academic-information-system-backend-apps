<?php

namespace App\Http\Resources\Academics;

use App\Http\Resources\Evaluations\RecommendationResource;
use App\Http\Resources\Utils\DateTimeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
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
      'nim' => $this->nim,
      'nik' => $this->nik,
      'name' => $this->name,
      'email' => $this->email,
      'birth_date' => $this->birth_date,
      'birth_place' => $this->birth_place,
      'gender' => $this->gender,
      'phone' => $this->phone,
      'religion' => $this->religion,
      'initial_registration_period' => $this->initial_registration_period,
      'origin_department' => $this->origin_department,
      'upbjj' => $this->upbjj,
      'status_registration' => $this->status_registration,
      'status_activity' => $this->status_activity,
      'student_photo_url' => $this->student_photo_url,
      'parent_name' => $this->parent_name,
      'parent_phone_number' => $this->parent_phone_number,
      'major' => MajorResource::make($this->major),
      'addresses' => StudentAddressResource::collection($this->whenLoaded('addresses')),
      'recommendations' => RecommendationResource::collection($this->whenLoaded('recommendations')),
      'domicile_address' => StudentAddressResource::make($this->whenLoaded('domicileAddress')),
      'id_card_address' => StudentAddressResource::make($this->whenLoaded('idCardAddress')),
      'created_at' => DateTimeResource::make($this->created_at),
      'updated_at' => DateTimeResource::make($this->updated_at)
    ];
  }
}
