<?php

namespace App\Services\Village;

use App\Enums\WhereOperator;
use App\Repositories\District\DistrictRepository;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Village\VillageRepository;

class VillageServiceImplement extends ServiceApi implements VillageService
{
  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Village";
  protected string $create_message = "Successfully created Village Data";
  protected string $update_message = "Successfully updated Village Data";
  protected string $delete_message = "Successfully deleted Village Data";
  protected string $error_message = "Error while performing action, please check log";

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected VillageRepository $mainRepository;
  protected DistrictRepository $districtRepository;

  public function __construct(
    VillageRepository $mainRepository,
    DistrictRepository $districtRepository
  ) {
    $this->mainRepository = $mainRepository;
    $this->districtRepository = $districtRepository;
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

      # Find District
      $district = $this->districtRepository->findOrFail($payload['districts']);

      # Check if District not found
      if (!$district) {
        return $this->setMessage($this->error_message)->toJson();
      }

      # Execute to Database
      $payload['full_code'] = $district->full_code . $payload['code'];
      $payload['district_id'] = $district->id;

      # Create and load relations
      $village = $this->mainRepository->create($payload);
      $village->load('district.regency.province');

      return $this->setMessage($this->create_message)
        ->setData($village)
        ->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleUpdate($request, \App\Models\Village $village)
  {
    try {
      # request
      $payload = $request->validated();

      # Find District
      $district = $this->districtRepository->findOrFail($payload['districts']);

      # Check if District not found
      if (!$district) {
        return $this->setMessage($this->error_message)->toJson();
      }

      # Execute to Database
      $payload['full_code'] = $district->full_code . $payload['code'];
      $payload['district_id'] = $district->id;

      # Update Database
      $village->update($payload);

      # Refresh model untuk mendapatkan relasi yang terupdate
      $village->refresh();

      return $this->setMessage($this->update_message)
        ->setData($village)
        ->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleDelete(\App\Models\Village $village)
  {
    try {
      $village->delete();
      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleBulkDelete(array $uuid)
  {
    try {
      $villages = $this->getWhere(
        wheres: [
          'uuid' => [
            'operator' => WhereOperator::In->value,
            'value' => $uuid
          ]
        ]
      )->get();

      foreach ($villages as $village) {
        $village->delete();
      }

      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }
}