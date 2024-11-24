<?php

namespace App\Services\District;

use App\Models\District;
use LaravelEasyRepository\BaseService;

interface DistrictService extends BaseService
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
  public function handleUpdate($request, District $district);
  public function handleDelete(District $district);
  public function handleBulkDelete(array $uuid);
}
