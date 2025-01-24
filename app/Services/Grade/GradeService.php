<?php

namespace App\Services\Grade;

use App\Models\Student;
use LaravelEasyRepository\BaseService;

interface GradeService extends BaseService
{
  public function query();
  public function getWhere(
    $wheres = [],
    $columns = '*',
    $comparisons = '=',
    $orderBy = null,
    $orderByType = null
  );
  public function handleStore($request, Student $student);
}
