<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Village extends Model
{
  use HasUuid;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'uuid',
    'district_id',
    'name',
    'code',
    'full_code',
    'pos_code',
  ];

  /**
   * Get the route key for the model.
   */
  public function getRouteKeyName(): string
  {
    return 'uuid';
  }

  /**
   * The relationships that should always be loaded.
   *
   * @var array<string>
   */
  protected $with = [
    'district.regency.province',
  ];

  /**
   * Get the district that owns the districts.
   *
   * @return BelongsTo
   */
  public function district(): BelongsTo
  {
    return $this->belongsTo(District::class);
  }
}
