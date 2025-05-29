<?php

namespace App\Repositories\Invoice;

use App\Models\Invoice;
use LaravelEasyRepository\Implementations\Eloquent;

class InvoiceRepositoryImplement extends Eloquent implements InvoiceRepository
{
    protected $model;

    public function __construct(Invoice $model)
    {
        $this->model = $model;
    }

    public function query()
    {
        return $this->model->newQuery();
    }

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
