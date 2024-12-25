<?php

namespace App\Models;

use App\Enums\Academics\AddressType;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAddress extends Model
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
    'village_id',
    'type',
    'address'
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
    'type' => AddressType::class
  ];

  /**
   * The relationships that should always be loaded.
   *
   * @var array<string>
   */
  protected $with = [
    'village'
  ];

  /**
   * Get the student associated with the student address.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class);
  }

  /**
   * Get the village associated with the student address.
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function village(): BelongsTo
  {
    return $this->belongsTo(Village::class);
  }
}
