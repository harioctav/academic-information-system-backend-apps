<?php

namespace App\Repositories\Regency;

use LaravelEasyRepository\Implementations\Eloquent;
use App\Models\Regency;

class RegencyRepositoryImplement extends Eloquent implements RegencyRepository
{
  protected Regency $model;

  public function __construct(Regency $model)
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
