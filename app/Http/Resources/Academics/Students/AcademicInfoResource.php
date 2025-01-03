<?php

namespace App\Http\Resources\Academics\Students;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicInfoResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'credits' => [
        'taken' => [
          'total' => $this['credit_has_been_taken'],
          'passed' => $this['credit_has_been_passed'],
          'ongoing' => $this['credit_being_taken'],
          'need_improvement' => $this['credit_need_improvement'],
        ],
        'not_taken' => [
          'total' => $this['total_credit_not_yet_taken'],
          'by_passed' => $this['total_credit_not_yet_taken_by_passed'],
        ],
        'transfer' => $this['transfer_credit'],
        'curriculum' => $this['credit_by_curriculum'],
        'total_required' => $this['total_course_credit'],
      ],
      'academic_performance' => [
        'gpa' => $this['gpa'],
        'percentage' => $this['percentage'],
        'has_grade_e' => $this['has_grade_e'],
        'quality_points' => $this['mutu'],
      ],
      'estimated_remaining_semesters' => $this['estimated_remaining_semesters'],
    ];
  }
}
