<?php

namespace App\Repositories\District;

use LaravelEasyRepository\Repository;

interface DistrictRepository extends Repository
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
