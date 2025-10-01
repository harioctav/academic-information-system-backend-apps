<?php

namespace App\Repositories\RegistrationBatch;

use LaravelEasyRepository\Repository;

interface RegistrationBatchRepository extends Repository
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
