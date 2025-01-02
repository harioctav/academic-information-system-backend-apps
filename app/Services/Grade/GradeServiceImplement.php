<?php

namespace App\Services\Grade;

use LaravelEasyRepository\ServiceApi;
use App\Repositories\Grade\GradeRepository;

class GradeServiceImplement extends ServiceApi implements GradeService{

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
     protected GradeRepository $mainRepository;

    public function __construct(GradeRepository $mainRepository)
    {
      $this->mainRepository = $mainRepository;
    }

    // Define your custom methods :)
}
