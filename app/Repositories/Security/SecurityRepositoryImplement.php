<?php

namespace App\Repositories\Security;

use App\Enums\WhereOperator;
use App\Models\LoginActivity;
use LaravelEasyRepository\Implementations\Eloquent;
use App\Models\Security;

class SecurityRepositoryImplement extends Eloquent implements SecurityRepository
{
  protected LoginActivity $model;

  public function __construct(
    LoginActivity $model
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
      foreach ($wheres as $key => $where) {
        if (is_array($where)) {
          $operator = $where['operator'] ?? WhereOperator::In->value;
          $value = $where['value'] ?? $where;

          switch (strtolower($operator)) {
            case WhereOperator::In->value:
              $query->whereIn($key, $value);
              break;
            case WhereOperator::NotIn->value:
              $query->whereNotIn($key, $value);
              break;
            case WhereOperator::Between->value:
              $query->whereBetween($key, $value);
              break;
            case WhereOperator::NotBetween->value:
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
