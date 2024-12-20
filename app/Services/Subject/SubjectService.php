<?php

namespace App\Services\Subject;

use App\Models\Subject;
use LaravelEasyRepository\BaseService;

interface SubjectService extends BaseService
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
  public function handleUpdate($request, Subject $subject);
  public function handleDelete(Subject $subject);
  public function handleBulkDelete(array $uuid);
}
