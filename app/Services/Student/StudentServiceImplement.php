<?php

namespace App\Services\Student;

use LaravelEasyRepository\ServiceApi;
use App\Repositories\Student\StudentRepository;

class StudentServiceImplement extends ServiceApi implements StudentService
{

  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Student";
  protected string $create_message = "Successfully created Student Data";
  protected string $update_message = "Successfully updated Student Data";
  protected string $delete_message = "Successfully deleted Student Data";

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected StudentRepository $mainRepository;

  public function __construct(StudentRepository $mainRepository)
  {
    $this->mainRepository = $mainRepository;
  }

  public function query()
  {
    return $this->mainRepository->query();
  }

  public function getWhere(
    $wheres = [],
    $columns = '*',
    $comparisons = '=',
    $orderBy = null,
    $orderByType = null
  ) {
    return $this->mainRepository->getWhere(
      wheres: $wheres,
      columns: $columns,
      comparisons: $comparisons,
      orderBy: $orderBy,
      orderByType: $orderByType
    );
  }
}
