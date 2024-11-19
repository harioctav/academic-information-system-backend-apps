<?php

namespace App\Services\Regency;

use App\Models\Regency;
use LaravelEasyRepository\BaseService;

interface RegencyService extends BaseService
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
  public function handleUpdate($request, Regency $regency);
  public function handleDelete(Regency $regency);
  public function handleBulkDelete(array $uuid);
}
