<?php

namespace App\Models;

use App\Enums\Academics\DegreeType;
use App\Enums\Academics\SubjectNote;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Major extends Model
{
  use HasUuid;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'uuid',
    'code',
    'name',
    'degree',
    'total_course_credit',
  ];

  /**
   * Get the route key for the model.
   */
  public function getRouteKeyName(): string
  {
    return 'uuid';
  }

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected $casts = [
    'degree' => DegreeType::class
  ];

  /**
   * Get the students for the district.
   *
   * @return HasMany
   */
  public function students(): HasMany
  {
    return $this->hasMany(Student::class);
  }

  /**
   * Get the subjects associated with this major.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function subjects(): BelongsToMany
  {
    return $this->belongsToMany(
      Subject::class,
      'major_has_subjects'
    )->using(MajorSubject::class)
      ->withPivot('semester')
      ->withTimestamps();
  }

  /**
   * Updates the total course credit for the major.
   * This method calculates the total course credit by:
   * 1. Grouping the subjects by semester.
   * 2. Separating the subjects into two groups: those with "PILIH SALAH SATU" in the subject_note, and those without.
   * 3. Adding the course credits of the subjects without "PILIH SALAH SATU".
   * 4. If there are subjects with "PILIH SALAH SATU", only adding the maximum course credit from that group.
   * 5. Updating the total_course_credit column in the majors table with the calculated total.
   */
  public function updateTotalCourseCredit()
  {
    $totalCourseCredit = 0;
    $subjects = $this->subjects;
    $subjectsBySemester = $subjects->groupBy('pivot.semester');

    foreach ($subjectsBySemester as $semester => $subjects) {
      // Pisahkan mata kuliah berdasarkan "PILIH SALAH SATU"
      $withPilihSalahSatu = $subjects->filter(function ($subject) {
        return str_contains($subject->subject_note, SubjectNote::PS->value);
      });

      $withoutPilihSalahSatu = $subjects->filter(function ($subject) {
        return !str_contains($subject->subject_note, SubjectNote::PS->value);
      });

      // Tambahkan total SKS dari mata kuliah tanpa "PILIH SALAH SATU"
      foreach ($withoutPilihSalahSatu as $subject) {
        $totalCourseCredit += $subject->course_credit; // Mengambil SKS dari kolom course_credit di tabel subjects
      }

      // Jika ada mata kuliah "PILIH SALAH SATU", hanya tambahkan salah satu dari grup ini
      if ($withPilihSalahSatu->isNotEmpty()) {
        $totalCourseCredit += $withPilihSalahSatu->max()->course_credit;
      }
    }

    // Update nilai total_course_credit pada tabel majors
    $this->update(['total_course_credit' => $totalCourseCredit]);
  }
}
