<?php

namespace App\Models;

use App\Enums\Academics\SubjectStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

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
}
