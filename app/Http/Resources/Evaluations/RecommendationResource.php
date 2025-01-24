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
      'semester' => $this->semester,
      'exam_period' => $this->exam_period,
      'recommendation_note' => $this->recommendation_note,
      'subject' => [
        'id' => $this->subject->id,
        'code' => $this->subject->code,
        'name' => $this->subject->name,
        'course_credit' => $this->subject->course_credit,
      ],
      'grade' => $this->subject->grades->first() ? [
        'grade' => $this->subject->grades->first()->grade,
        'quality' => $this->subject->grades->first()->quality,
        'mutu' => $this->subject->grades->first()->mutu,
      ] : null,
      'created_at' => DateTimeResource::make($this->created_at),
      'updated_at' => DateTimeResource::make($this->updated_at)
    ];
  }
}
