<?php

namespace App\Repositories\Province;

use LaravelEasyRepository\Implementations\Eloquent;
use App\Models\Province;

class ProvinceRepositoryImplement extends Eloquent implements ProvinceRepository{

    /**
    * Model class to be used in this repository for the common methods inside Eloquent
    * Don't remove or change $this->model variable name
    * @property Model|mixed $model;
    */
    protected Province $model;

    public function __construct(Province $model)
    {
        $this->model = $model;
    }

    // Write something awesome :)
}
