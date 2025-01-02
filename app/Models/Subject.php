<?php

namespace App\Models;

use App\Enums\Academics\SubjectStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
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
    'course_credit',
    'exam_time',
    'subject_status',
    'subject_note',
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
    'subject_status' => SubjectStatus::class,
  ];

  /**
   * Get the majors associated with this subject.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function majors(): BelongsToMany
  {
    return $this->belongsToMany(
      Major::class,
      'major_has_subjects'
    )
      ->using(MajorSubject::class)
      ->withPivot('semester')
      ->withTimestamps();
  }

  /**
   * Get the grades associated with this subject.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function grades(): HasMany
  {
    return $this->hasMany(Grade::class);
  }

  /**
   * Get the recommendations associated with this subject.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function recommendations(): HasMany
  {
    return $this->hasMany(Recommendation::class);
  }
}
