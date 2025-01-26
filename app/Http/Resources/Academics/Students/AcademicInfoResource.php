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
          'total' => $this['credit_has_been_taken'], // total SKS yang sudah ditempuh atau di ambil
          'passed' => $this['credit_has_been_passed'], // Total SKS yang sudah lulus
          'ongoing' => $this['credit_being_taken'], // Total SKS yang sedang diambil
          'need_improvement' => $this['credit_need_improvement'], // Total SKS yang perlu perbaikan atau dalam perbaikan
        ],
        'not_taken' => [
          'total' => $this['total_credit_not_yet_taken'], // total sks yang belum di tempuh
          'by_passed' => $this['total_credit_not_yet_taken_by_passed'], // total sks yang belum ditempuh berdasarkan kelulusan
        ],
        'transfer' => $this['transfer_credit'], // alih kredit
        'curriculum' => $this['credit_by_curriculum'], // berdasarkan kurikulum
        'total_required' => $this['total_course_credit'], // total sks wajib tempuh
      ],
      'academic_performance' => [
        'gpa' => $this['gpa'],
        'percentage' => $this['percentage'],
        'has_grade_e' => $this['has_grade_e'],
        'quality_points' => $this['mutu'], // Total nilai mutu
      ],
      'estimated_remaining_semesters' => $this['estimated_remaining_semesters'],
    ];
  }
}
