<?php

namespace App\Repositories\Province;

use LaravelEasyRepository\Implementations\Eloquent;
use App\Models\Province;

class ProvinceRepositoryImplement extends Eloquent implements ProvinceRepository
{
  protected Province $model;

  public function __construct(
    Province $model
  ) {
    $this->model = $model;
  }

  /**
   * Get a query builder instance for the Province model.
   */
  public function query()
  {
    return $this->model->query();
  }

  /**
   * Get a collection of records from the Province model that match the given criteria.
   */
  public function getWhere(
    $wheres = [],
    $columns = '*',
    $comparisons = '=',
    $orderBy = null,
    $orderByType = null
  ) {
    $query = $this->model->select($columns);

    if (!empty($wheres)) {
      foreach ($wheres as $key => $value) {
        if (is_array($value)) {
          $query = $query->whereIn($key, $value);
        } else {
          $query = $query->where($key, $comparisons, $value);
        }
      }
    }

    if ($orderBy) {
      $query = $query->orderBy($orderBy, $orderByType);
    }

    return $query;
  }
}
