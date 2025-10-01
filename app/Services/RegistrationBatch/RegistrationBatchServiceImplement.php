<?php

namespace App\Services\RegistrationBatch;

use App\Enums\WhereOperator;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\RegistrationBatch\RegistrationBatchRepository;
use App\Http\Resources\Finances\RegistrationBatchResource;
use App\Models\RegistrationBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegistrationBatchServiceImplement extends ServiceApi implements RegistrationBatchService
{
  protected string $title = "Pendaftaran";

  protected string $create_message = "Data Pendaftaran berhasil dibuat";
  protected string $update_message = "Data Pendaftaran berhasil diperbarui";
  protected string $delete_message = "Data Pendaftaran berhasil dihapus";

  protected RegistrationBatchRepository $mainRepository;

  public function __construct(RegistrationBatchRepository $mainRepository)
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

  public function handleStore($request)
  {
    try {
      $payload = $request->validated();
      $result = $this->mainRepository->create($payload);

      return $this->setMessage($this->create_message)
        ->setData(
          new RegistrationBatchResource($result)
        )
        ->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleUpdate($request, RegistrationBatch $registrationBatch)
  {
    try {
      $payload = $request->validated();
      $registrationBatch->update($payload);

      return $this->setMessage($this->update_message)
        ->setData(
          new RegistrationBatchResource($registrationBatch)
        )
        ->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleDelete(RegistrationBatch $registrationBatch)
  {
    try {
      $registrationBatch->delete();

      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleBulkDelete(array $uuid)
  {
    DB::beginTransaction();
    try {
      $registrationBatches = $this->getWhere(
        wheres: [
          'uuid' => [
            'operator' => WhereOperator::In->value,
            'value' => $uuid
          ]
        ]
      )->get();

      $deleted = 0;

      foreach ($registrationBatches as $registrationBatch) {
        $registrationBatch->delete();
        $deleted++;
      }

      DB::commit();

      return $this->setMessage("Berhasil menghapus {$deleted} Data {$this->title}")->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      return $this->setMessage($e->getMessage())->toJson();
    }
  }
}
