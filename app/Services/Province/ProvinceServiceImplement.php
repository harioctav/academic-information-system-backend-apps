<?php

namespace App\Services\Province;

use LaravelEasyRepository\ServiceApi;
use App\Repositories\Province\ProvinceRepository;

class ProvinceServiceImplement extends ServiceApi implements ProvinceService{

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
     protected ProvinceRepository $mainRepository;

    public function __construct(ProvinceRepository $mainRepository)
    {
      $this->mainRepository = $mainRepository;
    }

    // Define your custom methods :)
}
