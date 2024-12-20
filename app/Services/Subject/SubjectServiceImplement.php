<?php

namespace App\Services\Subject;

use LaravelEasyRepository\ServiceApi;
use App\Repositories\Subject\SubjectRepository;

class SubjectServiceImplement extends ServiceApi implements SubjectService
{
  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Subject";
  protected string $create_message = "Successfully created Subject Data";
  protected string $update_message = "Successfully updated Subject Data";
  protected string $delete_message = "Successfully deleted Subject Data";

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected SubjectRepository $mainRepository;

  public function __construct(SubjectRepository $mainRepository)
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
