<?php

namespace App\Repositories\Billing;

use App\Models\Billing;
use LaravelEasyRepository\Implementations\Eloquent;

class BillingRepositoryImplement extends Eloquent implements BillingRepository
{
    protected $model;

    public function __construct(Billing $model)
    {
        $this->model = $model;
    }

    /**
     * Get data with dynamic where clause
     *
     * @param array $wheres
     * @param mixed $columns
     * @param string|array $comparisons
     * @param string|null $orderBy
     * @param string|null $orderByType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWhere(
        $wheres = [],
        $columns = '*',
        $comparisons = '=',
        $orderBy = null,
        $orderByType = null
    ) {
        $query = $this->model->query();

        foreach ($wheres as $field => $condition) {
            $operator = $condition['operator'] ?? $comparisons;
            $value = $condition['value'] ?? $condition;

            if (in_array(strtolower($operator), ['in', 'not in'])) {
                $method = strtolower($operator) === 'in' ? 'whereIn' : 'whereNotIn';
                $query->$method($field, $value);
            } else {
                $query->where($field, $operator, $value);
            }
        }

        if ($orderBy) {
            $query->orderBy($orderBy, $orderByType ?? 'asc');
        }

        return $query->get($columns);
    }
}
