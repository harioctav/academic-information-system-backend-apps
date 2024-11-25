<?php

namespace App\Services\PermissionCategory;

use LaravelEasyRepository\BaseService;

interface PermissionCategoryService extends BaseService
{
  public function query();
  public function getWhere(
    $wheres = [],
    $columns = '*',
    $comparisons = '=',
    $orderBy = null,
    $orderByType = null
  );
}
