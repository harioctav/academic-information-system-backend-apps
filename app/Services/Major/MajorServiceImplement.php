<?php

namespace App\Services\Major;

use LaravelEasyRepository\ServiceApi;
use App\Repositories\Major\MajorRepository;

class MajorServiceImplement extends ServiceApi implements MajorService
{

  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Major";
  protected string $create_message = "Successfully created Major Data";
  protected string $update_message = "Successfully updated Major Data";
  protected string $delete_message = "Successfully deleted Major Data";

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected MajorRepository $mainRepository;

  public function __construct(MajorRepository $mainRepository)
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
