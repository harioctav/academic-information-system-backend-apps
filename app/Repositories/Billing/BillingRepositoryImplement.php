<?php

namespace App\Repositories\Billing;

use App\Models\Billing;
use App\Enums\WhereOperator;
use LaravelEasyRepository\Implementations\Eloquent;

class BillingRepositoryImplement extends Eloquent implements BillingRepository
{
  protected $model;

  public function __construct(Billing $model)
  {
    $this->model = $model;
  }

  public function query()
  {
    return $this->model->newQuery();
  }

  // public function getWhere(
  //   $wheres = [],
  //   $columns = '*',
  //   $comparisons = '=',
  //   $orderBy = null,
  //   $orderByType = null
  // ) {
  //   $query = $this->model->query();

  //   foreach ($wheres as $field => $condition) {
  //     $operator = $condition['operator'] ?? $comparisons;
  //     $value = $condition['value'] ?? $condition;

  //     if (in_array(strtolower($operator), ['in', 'not in'])) {
  //       $method = strtolower($operator) === 'in' ? 'whereIn' : 'whereNotIn';
  //       $query->$method($field, $value);
  //     } else {
  //       $query->where($field, $operator, $value);
  //     }
  //   }

  //   if ($orderBy) {
  //     $query->orderBy($orderBy, $orderByType ?? 'asc');
  //   }

  //   return $query->get($columns);
  // }

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
