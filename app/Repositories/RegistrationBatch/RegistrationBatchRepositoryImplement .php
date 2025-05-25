<?php

namespace App\Repositories\RegistrationBatch;

use App\Models\RegistrationBatch; // ganti sesuai model kamu
use LaravelEasyRepository\Implementations\Eloquent;
use Illuminate\Support\Facades\DB;

class RegistrationBatchRepositoryImplement extends Eloquent implements RegistrationBatchRepository
{
    /**
     * Model yang digunakan oleh repository ini
     *
     * @var \App\Models\RegistrationBatch
     */
    protected $model;

    public function __construct(RegistrationBatch $model)
    {
        $this->model = $model;
    }

    public function findByUuid(string $uuid)
    {
        return $this->model->where('uuid', $uuid)->first();
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
            $query->where($key, $comparisons, $value);
        }

        if ($orderBy) {
            $query->orderBy($orderBy, $orderByType ?? 'asc');
        }

        return $query->get($columns);
    }
}
