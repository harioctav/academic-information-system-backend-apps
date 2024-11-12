<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermissionCategory extends Model
{
  use HasUuid;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'uuid',
    'name',
  ];

  /**
   * Get the permissions associated with this permission category.
   *
   * @return HasMany
   */
  public function permissions(): HasMany
  {
    return $this->hasMany(Permission::class, 'permission_category_id');
  }
}
