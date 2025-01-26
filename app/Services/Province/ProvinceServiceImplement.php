<?php

namespace App\Services\Province;

use App\Enums\WhereOperator;
use App\Http\Resources\Locations\ProvinceResource;
use App\Models\Province;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Province\ProvinceRepository;
use Illuminate\Support\Facades\DB;

class ProvinceServiceImplement extends ServiceApi implements ProvinceService
{
  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Provinsi";
  protected string $create_message = "Data Provinsi berhasil dibuat";
  protected string $update_message = "Data Provinsi berhasil diperbarui";
  protected string $delete_message = "Data Provinsi berhasil dihapus";

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
      $payload = $request->validated();
      $result = $this->mainRepository->create($payload);

      /**
       * Returns a JSON response with a success message and the created province resource.
       *
       * @param $request
       * @return \Illuminate\Http\JsonResponse The JSON response.
       */
      return $this->setMessage($this->create_message)
        ->setData(
          new ProvinceResource($result)
        )
        ->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleUpdate($request, Province $province)
  {
    try {
      $payload = $request->validated();
      $province->update($payload);

      /**
       * Returns a JSON response with a success message and the updated province resource.
       *
       * @param $request
       * @param Province $province The province to be updated.
       * @return \Illuminate\Http\JsonResponse The JSON response.
       */
      return $this->setMessage($this->update_message)
        ->setData(
          new ProvinceResource($province)
        )
        ->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleDelete(Province $province)
  {
    try {
      $province->delete();

      /**
       * Returns a JSON response with a success message indicating the province has been deleted.
       *
       * @return \Illuminate\Http\JsonResponse The JSON response.
       */
      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleBulkDelete(array $uuid)
  {
    DB::beginTransaction();
    try {
      $provinces = $this->getWhere(
        wheres: [
          'uuid' => [
            'operator' => WhereOperator::In->value,
            'value' => $uuid
          ]
        ]
      )->get();

      $deleted = 0;

      foreach ($provinces as $province) {
        $province->delete();
        $deleted++;
      }

      DB::commit();

      /**
       * Returns a JSON response with a success message indicating the province has been deleted.
       *
       * @return \Illuminate\Http\JsonResponse The JSON response.
       */
      return $this->setMessage("Berhasil menghapus {$deleted} Data {$this->title}")->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }
}
