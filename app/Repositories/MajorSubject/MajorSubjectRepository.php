<?php

namespace App\Repositories\MajorSubject;

use LaravelEasyRepository\Repository;

interface MajorSubjectRepository extends Repository
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
