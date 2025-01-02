<?php

namespace App\Services\Recommendation;

use App\Models\Recommendation;
use App\Models\Student;
use LaravelEasyRepository\BaseService;

interface RecommendationService extends BaseService
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
  public function handleUpdate($request, Recommendation $recommendation);
  public function handleDelete(Recommendation $recommendation);
  public function handleBulkDelete(array $uuid);
}
