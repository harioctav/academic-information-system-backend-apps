<?php

namespace App\Services\Major;

use App\Models\Major;
use LaravelEasyRepository\BaseService;

interface MajorService extends BaseService
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
  public function handleUpdate($request, Major $major);
  public function handleDelete(Major $major);
  public function handleBulkDelete(array $uuid);
}
