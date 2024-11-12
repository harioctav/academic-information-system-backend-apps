<?php

namespace App\Repositories\Province;

use Illuminate\Database\Eloquent\Collection;
use LaravelEasyRepository\Repository;

interface ProvinceRepository extends Repository
{
  public function query();
  public function getWhere(
    $wheres = [],
    $columns = '*',
    $comparisons = '=',
    $orderBy = null,
    $orderByType = null
  ): Collection;
}
