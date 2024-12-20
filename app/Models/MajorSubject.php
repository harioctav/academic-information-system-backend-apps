<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MajorSubject extends Pivot
{
  use HasUuid;

  /**
   * Hooks that are executed when a MajorSubject model is created or deleted.
   * When a MajorSubject is created or deleted, the updateTotalCourseCredit method
   * is called on the associated Major model to update the total course credits.
   */
  protected static function booted()
  {
    static::created(function ($majorSubject) {
      $majorSubject->major->updateTotalCourseCredit();
    });

    static::deleted(function ($majorSubject) {
      $majorSubject->major->updateTotalCourseCredit();
    });
  }

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'major_has_subjects';

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'uuid',
    'major_id',
    'subject_id',
    'semester',
  ];

  /**
   * Get the route key for the model.
   */
  public function getRouteKeyName(): string
  {
    return 'uuid';
  }

  /**
   * Get the subject associated with this major subject.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function subject(): BelongsTo
  {
    return $this->belongsTo(Subject::class, 'subject_id');
  }

  /**
   * Get the major associated with this major subject.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function major(): BelongsTo
  {
    return $this->belongsTo(Major::class, 'major_id');
  }
}
