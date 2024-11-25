<?php

namespace App\Services\Permission;

use LaravelEasyRepository\ServiceApi;
use App\Repositories\Permission\PermissionRepository;

class PermissionServiceImplement extends ServiceApi implements PermissionService{

    /**
     * set title message api for CRUD
     * @param string $title
     */
     protected string $title = "";
     /**
     * uncomment this to override the default message
     * protected string $create_message = "";
     * protected string $update_message = "";
     * protected string $delete_message = "";
     */

     /**
     * don't change $this->mainRepository variable name
     * because used in extends service class
     */
     protected PermissionRepository $mainRepository;

    public function __construct(PermissionRepository $mainRepository)
    {
      $this->mainRepository = $mainRepository;
    }

    // Define your custom methods :)
}
