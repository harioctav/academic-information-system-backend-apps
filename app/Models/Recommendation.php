<?php

namespace App\Models;

use App\Enums\Evaluations\RecommendationNote;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Recommendation extends Model
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
    'semester',
    'exam_period',
    'recommendation_note',
  ];

  /**
   * The relationships that should always be loaded.
   *
   * @var array<string>
   */
  protected $with = [
    'student',
    'subject',
    'grade'
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
    'recommendation_note' => RecommendationNote::class,
  ];

  /**
   * Get the subject relationship for the recommendation.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function subject(): BelongsTo
  {
    return $this->belongsTo(Subject::class);
  }

  /**
   * Get the student relationship for the recommendation.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class);
  }

  /**
   * Get the grade relationship for the recommendation.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasOne
   */
  public function grade(): HasOne
  {
    return $this->hasOne(Grade::class, 'subject_id', 'subject_id')
      ->where('grades.student_id', $this->student_id);
  }
}
