<?php

namespace App\Http\Resources\Evaluations;

use App\Http\Resources\Academics\StudentResource;
use App\Http\Resources\Academics\SubjectResource;
use App\Http\Resources\Utils\DateTimeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationResource extends JsonResource
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
      'subject_id' => $this->subject_id,
      'student_id' => $this->student_id,
      'semester' => $this->semester,
      'exam_period' => $this->exam_period,
      'recommendation_note' => $this->recommendation_note,
      'student' => StudentResource::make($this->student),
      'subject' => SubjectResource::make($this->subject),
      'created_at' => DateTimeResource::make($this->created_at),
      'updated_at' => DateTimeResource::make($this->updated_at)
    ];
  }
}
