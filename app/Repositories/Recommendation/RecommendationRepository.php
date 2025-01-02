<?php

namespace App\Repositories\Recommendation;

use LaravelEasyRepository\Repository;

interface RecommendationRepository extends Repository
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
