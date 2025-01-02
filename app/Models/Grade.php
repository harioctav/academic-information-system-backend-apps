<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Grade extends Model
{
  use HasUuid;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'uuid',
    'student_id',
    'subject_id',
    'grade',
    'quality',
    'exam_period',
    'mutu',
    'grade_note',
  ];

  /**
   * The relationships that should always be loaded.
   *
   * @var array<string>
   */
  protected $with = [
    'student',
    'subject'
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected $casts = [
    'mutu' => 'decimal:1',
  ];

  /**
   * Get the route key for the model.
   */
  public function getRouteKeyName(): string
  {
    return 'uuid';
  }

  /**
   * Get the subject associated with the grade.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function subject(): BelongsTo
  {
    return $this->belongsTo(Subject::class);
  }

  /**
   * Get the student associated with the grade.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class);
  }

  /**
   * Get the recommendation associated with the student and subject.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasOne
   */
  public function recommendation(): HasOne
  {
    return $this->hasOne(Recommendation::class, 'student_id', 'student_id')
      ->where('subject_id', $this->subject_id);
  }

  /**
   * Get the semester associated with the grade based on the student's major and the subject.
   *
   * This method retrieves the semester information from the pivot table that connects the student's major and the subject.
   * If the pivot information is found, it returns the semester value. Otherwise, it returns 'Unknown Semester'.
   *
   * @return string The semester associated with the grade.
   */
  public function getSemesterAttribute()
  {
    // Ambil ID major dari student
    $majorId = $this->student->major_id;

    // Ambil semester dari tabel pivot berdasarkan major dan subject
    $pivot = $this->subject->majors->where('id', $majorId)->first();

    return $pivot ? $pivot->pivot->semester : 'Unknown Semester';
  }
}
