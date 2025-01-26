<?php

namespace App\Services\Regency;

use App\Enums\WhereOperator;
use App\Http\Resources\Locations\RegencyResource;
use App\Models\Regency;
use App\Repositories\Province\ProvinceRepository;
use App\Traits\LocationPayload;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Regency\RegencyRepository;

class RegencyServiceImplement extends ServiceApi implements RegencyService
{
  use LocationPayload;

  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Kabupaten atau Kota";

  protected string $create_message = "Data Kabupaten atau Kota berhasil dibuat";

  protected string $update_message = "Data Kabupaten atau Kota berhasil diperbarui";

  protected string $delete_message = "Data Kabupaten atau Kota berhasil dihapus";

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected RegencyRepository $mainRepository;
  protected ProvinceRepository $provinceRepository;

  public function __construct(
    RegencyRepository $mainRepository,
    ProvinceRepository $provinceRepository
  ) {
    $this->mainRepository = $mainRepository;
    $this->provinceRepository = $provinceRepository;
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
      $r = $request->validated();
      $payload = $this->prepareLocationPayload($r, 'provinces', $this->provinceRepository);

      # Create and load relations
      $regency = $this->mainRepository->create($payload);

      return $this->setMessage($this->create_message)
        ->setData(
          new RegencyResource($regency)
        )
        ->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleUpdate($request, Regency $regency)
  {
    try {
      $r = $request->validated();
      $payload = $this->prepareLocationPayload($r, 'provinces', $this->provinceRepository);

      # Update Data
      $regency->update($payload);
      $regency->refresh();

      return $this->setMessage($this->update_message)
        ->setData(
          new RegencyResource($regency)
        )
        ->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleDelete(Regency $regency)
  {
    try {
      $regency->delete();
      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleBulkDelete(array $uuid)
  {
    try {
      $regencies = $this->getWhere(
        wheres: [
          'uuid' => [
            'operator' => WhereOperator::In->value,
            'value' => $uuid
          ]
        ]
      )->get();

      $deleted = 0;

      foreach ($regencies as $regency) {
        $regency->delete();
        $deleted++;
      }

      return $this->setMessage("Berhasil menghapus {$deleted} Data {$this->title}")->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }
}
