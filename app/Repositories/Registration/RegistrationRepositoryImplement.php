<?php

namespace App\Repositories\Registration;

use App\Models\Registration;
use App\Enums\WhereOperator;
use LaravelEasyRepository\Implementations\Eloquent;
use Illuminate\Support\Facades\DB;

class RegistrationRepositoryImplement extends Eloquent implements RegistrationRepository
{
  /**
   * Model yang digunakan oleh repository ini
   *
   * @var \App\Models\Registration
   */
  protected $model;

  public function __construct(Registration $model)
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
          $operator = $where['operator'] ?? $comparisons;
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
              break;
          }
        } else {
          $query->where($key, $comparisons, $where);
        }
      }
    }

    if ($orderBy) {
      $query->orderBy($orderBy, $orderByType ?? 'asc');
    }

    return $query->get();
  }
}
