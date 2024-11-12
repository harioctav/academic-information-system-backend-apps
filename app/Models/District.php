<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
  use HasUuid;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'uuid',
    'regency_id',
    'name',
    'code',
    'full_code',
  ];

  /**
   * The relationships that should always be loaded.
   *
   * @var array<string>
   */
  protected $with = [
    'regency.province'
  ];

  /**
   * Get the route key for the model.
   */
  public function getRouteKeyName(): string
  {
    return 'uuid';
  }

  /**
   * Get the regency that owns the regencies.
   *
   * @return BelongsTo
   */
  public function regency(): BelongsTo
  {
    return $this->belongsTo(Regency::class, 'regency_id');
  }

  /**
   * Get the villages for the district.
   *
   * @return HasMany
   */
  public function villages(): HasMany
  {
    return $this->hasMany(Village::class, 'district_id');
  }
}
