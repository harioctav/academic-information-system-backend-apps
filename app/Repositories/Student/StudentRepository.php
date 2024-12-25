<?php

namespace App\Repositories\Student;

use LaravelEasyRepository\Repository;

interface StudentRepository extends Repository
{
  public function query();
  public function getWhere(
    $wheres = [],
    $columns = '*',
    $comparisons = '=',
    $orderBy = null,
    $orderByType = null
  );
}
