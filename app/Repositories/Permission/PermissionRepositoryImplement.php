<?php

namespace App\Repositories\Permission;

use LaravelEasyRepository\Implementations\Eloquent;
use App\Models\Permission;

class PermissionRepositoryImplement extends Eloquent implements PermissionRepository{

    /**
    * Model class to be used in this repository for the common methods inside Eloquent
    * Don't remove or change $this->model variable name
    * @property Model|mixed $model;
    */
    protected Permission $model;

    public function __construct(Permission $model)
    {
        $this->model = $model;
    }

    // Write something awesome :)
}
