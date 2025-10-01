<?php

namespace App\Repositories\Payment;

use App\Enums\WhereOperator;
use App\Models\Payment;
use LaravelEasyRepository\Implementations\Eloquent;

class PaymentRepositoryImplement extends Eloquent implements PaymentRepository
{
  protected $model;

  public function __construct(Payment $model)
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
    $query = $this->model->query();

    foreach ($wheres as $key => $value) {
      if (is_array($value) && isset($value['operator']) && isset($value['value'])) {
        // Handle special operators like WhereOperator::In
        $operator = $value['operator'];
        $val = $value['value'];

        if ($operator === WhereOperator::In->value) {
          $query->whereIn($key, $val);
        } elseif ($operator === WhereOperator::NotIn->value) {
          $query->whereNotIn($key, $val);
        } else {
          $query->where($key, $operator, $val);
        }
      } else {
        // Handle simple where conditions
        $query->where($key, $comparisons, $value);
      }
    }

    if ($orderBy) {
      $query->orderBy($orderBy, $orderByType ?? 'asc');
    }

    return $query->get($columns);
  }

  public function getWithRelationsPaginated($perPage)
  {
    return $this->model->with(['student', 'registration', 'billing'])->latest()->paginate($perPage);
  }

  public function findWithRelations($id)
  {
    return $this->model->with(['student', 'registration', 'billing'])->findOrFail($id);
  }
}
