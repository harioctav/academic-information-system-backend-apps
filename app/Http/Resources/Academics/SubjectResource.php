<?php

namespace App\Http\Resources\Academics;

use App\Http\Resources\Evaluations\GradeResource;
use App\Http\Resources\Evaluations\RecommendationResource;
use App\Http\Resources\Utils\DateTimeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
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
      'course_credit' => $this->course_credit,
      'subject_status' => $this->subject_status,
      'exam_time' => $this->exam_time,
      'subject_note' => $this->subject_note,
      'semester' => $this->when(isset($this->semester), $this->semester),
      'grades' => GradeResource::collection($this->whenLoaded('grades')),
      'recommendations' => RecommendationResource::collection($this->whenLoaded('recommendations')),
      'created_at' => DateTimeResource::make($this->created_at),
      'updated_at' => DateTimeResource::make($this->updated_at),
    ];
  }
}
