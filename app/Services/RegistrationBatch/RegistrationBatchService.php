<?php

namespace App\Services\RegistrationBatch;

use App\Models\RegistrationBatch;
use LaravelEasyRepository\BaseService;

interface RegistrationBatchService extends BaseService
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
  public function handleUpdate($request, RegistrationBatch $registrationBatch);
  public function handleDelete(RegistrationBatch $registrationBatch);
  public function handleBulkDelete(array $uuid);
}
