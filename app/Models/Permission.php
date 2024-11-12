<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission as ModelPermission;

class Permission extends ModelPermission
{
  use HasUuid;

  /**
   * Get the route key for the model.
   */
  public function getRouteKeyName(): string
  {
    return 'uuid';
  }

  /**
   * Get the permission category that this permission belongs to.
   *
   * @return BelongsTo
   */
  public function permissionCategory(): BelongsTo
  {
    return $this->belongsTo(PermissionCategory::class, 'permission_category_id');
  }
}
