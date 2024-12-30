<?php

namespace App\Services\Village;

use App\Models\Village;
use LaravelEasyRepository\BaseService;

interface VillageService extends BaseService
{
  public function query();
  public function getWhere(
    $wheres = [],
    $columns = '*',
    $comparisons = '=',
    $orderBy = null,
    $orderByType = null
  );
  public function findById(int $id);
  public function handleStore($request);
  public function handleUpdate($request, Village $village);
  public function handleDelete(Village $village);
  public function handleBulkDelete(array $uuid);
}
