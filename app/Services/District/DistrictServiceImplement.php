<?php

namespace App\Services\District;

use App\Enums\WhereOperator;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\District\DistrictRepository;
use App\Repositories\Regency\RegencyRepository;

class DistrictServiceImplement extends ServiceApi implements DistrictService
{
  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "District";
  protected string $create_message = "Successfully created District Data";
  protected string $update_message = "Successfully updated District Data";
  protected string $delete_message = "Successfully deleted District Data";
  protected string $error_message = "Error while performing action, please check log";

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected DistrictRepository $mainRepository;
  protected RegencyRepository $regencyRepository;

  public function __construct(
    DistrictRepository $mainRepository,
    RegencyRepository $regencyRepository
  ) {
    $this->mainRepository = $mainRepository;
    $this->regencyRepository = $regencyRepository;
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
      # request
      $payload = $request->validated();

      # Find Regency
      $regency = $this->regencyRepository->findOrFail($payload['regencies']);

      # Check if Regency not found
      if (!$regency) {
        return $this->setMessage($this->error_message)->toJson();
      }

      # Execute to Database
      $payload['full_code'] = $regency->full_code . $payload['code'];
      $payload['regency_id'] = $regency->id;

      # Create and load relations
      $district = $this->mainRepository->create($payload);
      $district->load('regency.province');

      return $this->setMessage($this->create_message)
        ->setData($district)
        ->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleUpdate($request, \App\Models\District $district)
  {
    try {
      # request
      $payload = $request->validated();

      # Find Regency
      $regency = $this->regencyRepository->findOrFail($payload['regencies']);

      # Check if Regency not found
      if (!$regency) {
        return $this->setMessage($this->error_message)->toJson();
      }

      # Execute to Database
      $payload['full_code'] = $regency->full_code . $payload['code'];
      $payload['regency_id'] = $regency->id;

      # Update Database
      $district->update($payload);

      # Refresh model untuk mendapatkan relasi yang terupdate
      $district->refresh();

      return $this->setMessage($this->update_message)
        ->setData($district)
        ->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleDelete(\App\Models\District $district)
  {
    try {
      $district->delete();
      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleBulkDelete(array $uuid)
  {
    try {
      $districts = $this->getWhere(
        wheres: [
          'uuid' => [
            'operator' => WhereOperator::In->value,
            'value' => $uuid
          ]
        ]
      )->get();

      foreach ($districts as $district) {
        $district->delete();
      }

      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }
}
