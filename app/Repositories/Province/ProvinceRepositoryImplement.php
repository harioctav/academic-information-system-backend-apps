<?php

namespace App\Repositories\Province;

use LaravelEasyRepository\Implementations\Eloquent;
use App\Models\Province;
use Illuminate\Database\Eloquent\Collection;

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
   *
   * @param array $wheres Key-value pairs of where clauses to filter the results. If the value is an array, it will be used in a whereIn() clause.
   * @param string|array $columns The columns to select from the model. Defaults to '*'.
   * @param string $comparisons The comparison operator to use in the where clauses. Defaults to '='.
   * @param string|null $orderBy The column to order the results by.
   * @param string|null $orderByType The direction to order the results by ('asc' or 'desc'). Defaults to null.
   * @return Collection
   */
  public function getWhere(
    $wheres = [],
    $columns = '*',
    $comparisons = '=',
    $orderBy = null,
    $orderByType = null
  ): Collection {
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

    return $query->get();
  }
}
