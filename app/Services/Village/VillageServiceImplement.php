<?php

namespace App\Services\Village;

use App\Enums\WhereOperator;
use App\Http\Resources\Locations\VillageResource;
use App\Repositories\District\DistrictRepository;
use App\Traits\LocationPayload;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Village\VillageRepository;

class VillageServiceImplement extends ServiceApi implements VillageService
{
  use LocationPayload;

  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Desa atau Kelurahan";

  protected string $create_message = "Data Desa atau Kelurahan berhasil dibuat";

  protected string $update_message = "Data Desa atau Kelurahan berhasil diperbarui";

  protected string $delete_message = "Data Desa atau Kelurahan berhasil dihapus";

  protected string $error_message = "Terjadi kesalahan saat melakukan tindakan, silakan periksa log";

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

  public function findById(int $id)
  {
    try {
      $village = $this->mainRepository->findOrFail($id);

      return $this->setData(new VillageResource($village))->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleStore($request)
  {
    try {
      # request
      $payload = $this->prepareLocationPayload($request->validated(), 'districts', $this->districtRepository);

      # Create and load relations
      $village = $this->mainRepository->create($payload);

      return $this->setMessage($this->create_message)
        ->setData(
          new VillageResource($village)
        )
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
      $payload = $this->prepareLocationPayload($request->validated(), 'districts', $this->districtRepository);

      # Update Database
      $village->update($payload);

      return $this->setMessage($this->update_message)
        ->setData(
          new VillageResource($village)
        )
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
