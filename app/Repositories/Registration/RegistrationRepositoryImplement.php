<?php

namespace App\Repositories\Registration;

use App\Models\Registration; // ganti sesuai model kamu
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
