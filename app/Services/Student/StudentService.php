<?php

namespace App\Services\Student;

use App\Models\Student;
use LaravelEasyRepository\BaseService;

interface StudentService extends BaseService
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
  public function handleUpdate($request, Student $student);
  public function handleDestroy(Student $student);
  public function handleDeleteImage(Student $student);
  public function handleBulkDelete(array $uuid);
}
