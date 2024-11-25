<?php

namespace App\Repositories\Village;

use LaravelEasyRepository\Implementations\Eloquent;
use App\Models\Village;

class VillageRepositoryImplement extends Eloquent implements VillageRepository
{
  /**
   * Model class to be used in this repository for the common methods inside Eloquent
   * Don't remove or change $this->model variable name
   */
  protected Village $model;

  public function __construct(Village $model)
  {
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
      foreach ($wheres as $key => $where) {
        if (is_array($where)) {
          $operator = $where['operator'] ?? 'in';
          $value = $where['value'] ?? $where;

          switch (strtolower($operator)) {
            case 'in':
              $query->whereIn($key, $value);
              break;
            case 'not in':
              $query->whereNotIn($key, $value);
              break;
            case 'between':
              $query->whereBetween($key, $value);
              break;
            case 'not between':
              $query->whereNotBetween($key, $value);
              break;
            default:
              $query->where($key, $operator, $value);
          }
        } else {
          $query->where($key, $comparisons, $where);
        }
      }
    }

    if ($orderBy) {
      $query->orderBy($orderBy, $orderByType);
    }

    return $query;
  }
}
