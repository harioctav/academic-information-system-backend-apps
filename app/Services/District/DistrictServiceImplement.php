<?php

namespace App\Services\District;

use App\Enums\WhereOperator;
use App\Http\Resources\Locations\DistrictResource;
use App\Traits\LocationPayload;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\District\DistrictRepository;
use App\Repositories\Regency\RegencyRepository;

class DistrictServiceImplement extends ServiceApi implements DistrictService
{
  use LocationPayload;

  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Kecamatan";

  protected string $create_message = "Data Kecamatan berhasil dibuat";

  protected string $update_message = "Data Kecamatan berhasil diperbarui";

  protected string $delete_message = "Data Kecamatan berhasil dihapus";

  protected string $error_message = "Terjadi kesalahan saat melakukan tindakan, silakan periksa log";

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
      $payload = $this->prepareLocationPayload($request->validated(), 'regencies', $this->regencyRepository);

      # Create and load relations
      $district = $this->mainRepository->create($payload);

      return $this->setMessage($this->create_message)
        ->setData(
          new DistrictResource($district)
        )
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
      $payload = $this->prepareLocationPayload($request->validated(), 'regencies', $this->regencyRepository);

      # Update Database
      $district->update($payload);

      return $this->setMessage($this->update_message)
        ->setData(
          new DistrictResource($district)
        )
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
