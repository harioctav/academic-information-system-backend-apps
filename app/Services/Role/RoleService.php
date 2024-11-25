<?php

namespace App\Services\Role;

use App\Models\Role;
use LaravelEasyRepository\BaseService;

interface RoleService extends BaseService
{
  public function query();
  public function getWhere(
    $wheres = [],
    $columns = '*',
    $comparisons = '=',
    $orderBy = null,
    $orderByType = null
  );
  public function handleStore($request);
  public function handleUpdate($request, Role $role);
  public function handleDelete(Role $role);
  public function handleBulkDelete(array $uuid);
}
