<?php

namespace App\Services\Province;

use App\Models\Province;
use LaravelEasyRepository\BaseService;

interface ProvinceService extends BaseService
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
  public function handleUpdate($request, Province $province);
  public function handleDelete(Province $province);
  public function handleBulkDelete(array $uuid);
}
