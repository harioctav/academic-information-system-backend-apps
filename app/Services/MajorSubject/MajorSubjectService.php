<?php

namespace App\Services\MajorSubject;

use App\Models\Major;
use App\Models\MajorSubject;
use LaravelEasyRepository\BaseService;

interface MajorSubjectService extends BaseService
{
  public function query();
  public function getWhere(
    $wheres = [],
    $columns = '*',
    $comparisons = '=',
    $orderBy = null,
    $orderByType = null
  );
  public function handleStore(Major $major, $request);
  public function handleDestroy(Major $major, MajorSubject $majorSubject);
  public function handleBulkDelete(array $uuid);
}
