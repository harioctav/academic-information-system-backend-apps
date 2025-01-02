<?php

namespace App\Repositories\Recommendation;

use App\Enums\WhereOperator;
use LaravelEasyRepository\Implementations\Eloquent;
use App\Models\Recommendation;

class RecommendationRepositoryImplement extends Eloquent implements RecommendationRepository
{
  /**
   * The Recommendation model used in this repository.
   */
  protected Recommendation $model;

  /**
   * Constructs a new instance of the RecommendationRepositoryImplement class, injecting the Recommendation model.
   *
   * @param Recommendation $model The Recommendation model to be used in this repository.
   */
  public function __construct(
    Recommendation $model
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
