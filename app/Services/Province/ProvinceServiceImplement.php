<?php

namespace App\Services\Province;

use App\Enums\WhereOperator;
use App\Models\Province;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Province\ProvinceRepository;
use Illuminate\Support\Facades\Log;

class ProvinceServiceImplement extends ServiceApi implements ProvinceService
{
  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Province";
  protected string $create_message = "Successfully created Province Data";
  protected string $update_message = "Successfully updated Province Data";
  protected string $delete_message = "Successfully deleted Province Data";

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected ProvinceRepository $mainRepository;

  public function __construct(
    ProvinceRepository $mainRepository
  ) {
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

  public function handleStore($request)
  {
    try {
      $result = $this->mainRepository->create($request->validated());
      return $this->setMessage($this->create_message)
        ->setData($result)
        ->toJson();
    } catch (\Exception $exception) {
      $this->exceptionResponse($exception);
      return null;
    }
  }

  public function handleUpdate($request, Province $province)
  {
    try {
      $province->update($request->validated());
      return $this->setMessage($this->update_message)
        ->setData($province)
        ->toJson();
    } catch (\Exception $exception) {
      $this->exceptionResponse($exception);
      return null;
    }
  }

  public function handleDelete(Province $province)
  {
    try {
      $province->delete();
      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $exception) {
      $this->exceptionResponse($exception);
      return null;
    }
  }

  public function handleBulkDelete(array $uuid)
  {
    try {
      $provinces = $this->getWhere(
        wheres: [
          'uuid' => [
            'operator' => WhereOperator::In->value,
            'value' => $uuid
          ]
        ]
      )->get();

      foreach ($provinces as $province) {
        $province->delete();
      }

      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $exception) {
      $this->exceptionResponse($exception);
      return null;
    }
  }
}
