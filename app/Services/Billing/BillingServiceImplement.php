<?php

namespace App\Services\Billing;

use App\Http\Resources\Finances\BillingResource;
use App\Models\Billing;
use App\Repositories\Billing\BillingRepository;
use App\Enums\WhereOperator;
use LaravelEasyRepository\ServiceApi;
use Illuminate\Support\Str;

class BillingServiceImplement extends ServiceApi implements BillingService
{
  protected string $title = "Tagihan";
  protected string $create_message = "Data Billing berhasil dibuat";
  protected string $update_message = "Data Billing berhasil diperbarui";
  protected string $delete_message = "Data Billing berhasil dihapus";
  protected string $error_message = "Terjadi kesalahan saat memproses data";

  protected BillingRepository $mainRepository;

  public function __construct(BillingRepository $mainRepository)
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
      $data = $request->validated();
      $data['uuid'] = Str::uuid();

      // Format tanggal: yyyyMMdd
      $datePrefix = now()->format('Ymd'); // Contoh: 20250326

      // Cari billing terakhir hari ini
      $lastBilling = $this->mainRepository->query()
        ->whereDate('created_at', now()->toDateString())
        ->orderByDesc('billing_code')
        ->first();

      // Ambil nomor terakhir jika ada
      $lastNumber = 0;
      if ($lastBilling && preg_match('/BILL-' . $datePrefix . '(\d+)/', $lastBilling->billing_code, $matches)) {
        $lastNumber = (int) $matches[1];
      }

      // Tambahkan 1, dan format jadi 4 digit (misal: 0001)
      $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

      // Gabungkan jadi billing code akhir
      $data['billing_code'] = 'BILL-' . $datePrefix . $newNumber;

      $billing = $this->mainRepository->create($data);

      // Load relationships to ensure consistent data
      $billing->load(['student', 'registration']);

      return $this->setMessage($this->create_message)
        ->setData(new BillingResource($billing))
        ->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleUpdate($request, Billing $billing)
  {
    try {
      $billing->update($request->validated());

      // Reload the model with relationships to ensure consistent data
      $billing->load(['student', 'registration']);

      return $this->setMessage($this->update_message)
        ->setData(new BillingResource($billing))
        ->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleDelete(Billing $billing)
  {
    try {
      $billing->delete();

      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleBulkDelete(array $uuid)
  {
    try {

      $billings =  $this->getWhere(
        wheres: [
          'uuid' => [
            'operator' => WhereOperator::In->value,
            'value' => $uuid
          ]
        ]
      )->get();

      $deleted = 0;
      foreach ($billings as $billing) {
        $billing->delete();
        $deleted++;
      }

      return $this->setMessage("Berhasil menghapus {$deleted} Data {$this->title}")->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }
}
