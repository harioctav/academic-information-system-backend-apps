<?php

namespace App\Helpers;

use App\Enums\Evaluations\GradeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Helper
{
  public const All = 'Semua Data';
  public const DefaultPassword = 'IniP4ssw0rd@!!';
  public const NewPassword = 'IniP4ssw0rd@!!B4ru';

  const LocationHierarchy = [
    'village' => [
      'district' => 'district',
      'regency' => 'district.regency',
      'province' => 'district.regency.province'
    ],
    'district' => [
      'regency' => 'regency',
      'province' => 'regency.province'
    ],
    'regency' => [
      'province' => 'province'
    ]
  ];

  public static function handleDeleteFile(
    Model $model,
    string $columns
  ) {
    // 
  }

  public static function calculateGPA($studentId)
  {
    // Ambil data nilai mahasiswa
    $grades = DB::table('grades')
      ->join('subjects', 'grades.subject_id', '=', 'subjects.id')
      ->where('grades.student_id', $studentId)
      ->select('grades.grade', 'subjects.course_credit')
      ->get();

    // Definisikan nilai untuk setiap grade
    $gradePoints = [
      GradeType::A->value => 4.00,
      GradeType::AMin->value => 3.70,
      GradeType::B->value => 3.00,
      GradeType::BMin->value => 2.70,
      GradeType::C->value => 2.00,
      GradeType::CMin->value => 1.70,
      GradeType::D->value => 1.00,
      GradeType::E->value => 0.00,
    ];

    $totalQualityPoints = 0;
    $totalCredits = 0;

    foreach ($grades as $grade) :
      $gradePoint = $gradePoints[$grade->grade] ?? 0;
      $credit = $grade->course_credit;
      $qualityPoints = $gradePoint * $credit;
      $totalQualityPoints += $qualityPoints;
      $totalCredits += $credit;
    endforeach;

    // Hitung IPK
    $gpa = $totalCredits > 0 ? $totalQualityPoints / $totalCredits : 0;

    return number_format($gpa, 2);
  }
}
