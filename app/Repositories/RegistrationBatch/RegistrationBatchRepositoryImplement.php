<?php

namespace App\Repositories\RegistrationBatch;

use App\Enums\WhereOperator;
use App\Models\RegistrationBatch;
use LaravelEasyRepository\Implementations\Eloquent;
use Illuminate\Support\Facades\DB;

class RegistrationBatchRepositoryImplement extends Eloquent implements RegistrationBatchRepository
{
  /**
   * Model yang digunakan oleh repository ini
   *
   * @var \App\Models\RegistrationBatch
   */
  protected RegistrationBatch $model;

  public function __construct(RegistrationBatch $model)
  {
    $this->model = $model;
  }

  public function query()
  {
    return $this->model->query();
  }

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
