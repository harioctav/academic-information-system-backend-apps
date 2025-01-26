<?php

namespace App\Http\Resources\Evaluations;

use App\Http\Resources\Utils\DateTimeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeResource extends JsonResource
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
      'grade' => $this->grade,
      'quality' => $this->quality,
      'exam_period' => $this->exam_period,
      'mutu' => $this->mutu,
      'grade_note' => $this->grade_note,
      'student' => $this->when($this->student, [
        'id' => $this->student?->id,
        'uuid' => $this->student?->uuid,
        'nim' => $this->student?->nim,
        'name' => $this->student?->name,
        'major' => $this->when($this->student?->major, [
          'id' => $this->student?->major?->id,
          'uuid' => $this->student?->major?->uuid,
          'name' => $this->student?->major?->name,
          'code' => $this->student?->major?->code,
        ]),
      ]),
      'subject' => [
        'id' => $this->subject->id,
        'uuid' => $this->subject->uuid,
        'code' => $this->subject->code,
        'name' => $this->subject->name,
        'course_credit' => $this->subject->course_credit,
      ],
      'recommendation' => [
        'id' => $this->recommendation_id,
        'uuid' => $this->recommendation_uuid,
        'recommendation_note' => $this->recommendation_note,
        'semester' => $this->recommendation_semester,
      ],
      'created_at' => DateTimeResource::make($this->created_at),
      'updated_at' => DateTimeResource::make($this->updated_at)
    ];
  }
}
