<?php

namespace App\Models;

use App\Enums\Locations\RegencyType;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Regency extends Model
{
  use HasUuid;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'uuid',
    'province_id',
    'type',
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
    'province'
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'type' => RegencyType::class
    ];
  }

  /**
   * Get the route key for the model.
   */
  public function getRouteKeyName(): string
  {
    return 'uuid';
  }

  /**
   * Get the province that owns the provinces.
   *
   * @return BelongsTo
   */
  public function province(): BelongsTo
  {
    return $this->belongsTo(Province::class, 'province_id');
  }

  /**
   * Get the districts for the regency.
   *
   * @return HasMany
   */
  public function districts(): HasMany
  {
    return $this->hasMany(District::class, 'regency_id');
  }

  /**
   * Get the villages for the regency through the districts.
   *
   * @return HasManyThrough
   */
  public function villages(): HasManyThrough
  {
    return $this->hasManyThrough(Village::class, District::class);
  }

  /**
   * Get the formatted name of the regency, which includes the type and name.
   *
   * @return string
   */
  public function getFormattedNameAttribute(): string
  {
    return "{$this->type->value} {$this->name}";
  }
}
