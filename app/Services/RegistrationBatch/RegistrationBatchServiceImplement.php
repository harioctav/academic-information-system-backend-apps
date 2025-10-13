<?php

namespace App\Services\RegistrationBatch;

use App\Enums\GeneralConstant;
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
  protected string $status_change_message = "Status Batch Pendaftaran berhasil diubah";

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

  public function handleChangeStatus(RegistrationBatch $registrationBatch)
  {
    try {
      DB::beginTransaction();

      // Get old status
      $oldStatus = $registrationBatch->status->value;

      // Determine New Status
      $newStatus = $oldStatus == GeneralConstant::Active->value
        ? GeneralConstant::InActive->value
        : GeneralConstant::Active->value;

      // Check if trying to activate and there's already an active batch
      if ($newStatus == GeneralConstant::Active->value) {
        $activeBatch = $this->getWhere(
          wheres: [
            'status' => GeneralConstant::Active->value
          ]
        )->first();

        // If there's an active batch and it's not the current one
        if ($activeBatch && $activeBatch->id !== $registrationBatch->id) {
          DB::rollBack();
          return $this->setCode(422)->setMessage("Tidak dapat mengaktifkan batch ini karena sudah ada batch lain yang aktif. Silakan nonaktifkan batch yang aktif terlebih dahulu.")->toJson();
        }
      }

      // Change Status
      $this->mainRepository->update($registrationBatch->id, ['status' => $newStatus]);
      DB::commit();

      return $this->setMessage($this->status_change_message)->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      return $this->setMessage($e->getMessage())->toJson();
    }
  }
}
