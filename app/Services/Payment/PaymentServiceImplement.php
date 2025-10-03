<?php

namespace App\Services\Payment;

use App\Enums\WhereOperator;
use App\Http\Resources\Finances\PaymentResource;
use App\Models\Payment;
use Illuminate\Support\Str;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Payment\PaymentRepository;
use App\Services\Invoice\InvoiceService;
use Illuminate\Support\Facades\Storage;
use App\Traits\FileUpload;

class PaymentServiceImplement extends ServiceApi implements PaymentService
{
  use FileUpload;
  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Pembayaran";
  protected string $create_message = "Data Pembayaran berhasil dibuat";
  protected string $update_message = "Data Pembayaran berhasil diperbarui";
  protected string $delete_message = "Data Pembayaran berhasil dihapus";
  protected string $error_message = "Terjadi kesalahan saat memproses data";

  protected $mainRepository;
  protected $invoiceService;

  public function __construct(PaymentRepository $mainRepository, InvoiceService $invoiceService)
  {
    $this->mainRepository = $mainRepository;
    $this->invoiceService = $invoiceService;
  }

  public function query()
  {
    return $this->mainRepository->query();
  }

  public function handleStore($request)
  {
    try {
      $data = $request->validated();
      $data['uuid'] = Str::uuid()->toString();

      // handle file upload for proof_of_payment
      $photoPath = null;

      if ($request->hasFile('proof_of_payment')) {
        $result = $this->upload(
          file: $request->file('proof_of_payment'),
          path: 'image/proof_of_payments',
        );

        if ($result['success']) {
          $photoPath = $result['path'];
        } else {
          return $this->setMessage($this->error_message)->toJson();
        }
      }

      if ($photoPath) {
        $data['proof_of_payment'] = $photoPath;
      }

      $payment = $this->mainRepository->create($data);

      return $this->setMessage($this->create_message)
        ->setData(new PaymentResource($payment->load(['student', 'billing'])))
        ->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleUpdate($request, Payment $payment)
  {
    try {
      $data = $request->validated();

      // Handle file upload for proof_of_payment if provided
      if ($request->hasFile('proof_of_payment')) {
        $result = $this->upload(
          file: $request->file('proof_of_payment'),
          path: 'image/proof_of_payments',
        );

        if ($result['success']) {
          $data['proof_of_payment'] = $result['path'];
        } else {
          return $this->setMessage($this->error_message)->toJson();
        }
      }

      $payment->update($data);

      return $this->setMessage($this->update_message)
        ->setData(new PaymentResource($payment->load(['student', 'billing', 'invoice'])))
        ->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleUpdateStatus(String $status, Payment $payment)
  {
    try {
      $payment->update([
        'payment_status' => $status
      ]);

      if ($payment) {
        // create invoice if payment status is paid
        if ($payment->payment_status === 'confirmed' && $payment->billing && !$payment->billing->invoice_id) {
          $this->invoiceService->createInvoice($payment);
        }
      }

      return $this->setMessage($this->update_message)
        ->setData([new PaymentResource($payment->load(['student', 'billing', 'invoice']))])
        ->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleShow(string $uuid): Payment
  {
    return Payment::where('uuid', $uuid)->with(['student', 'billing', 'invoice'])->firstOrFail();
  }

  public function handleDelete(Payment $payment)
  {
    try {
      $payment->delete();

      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleBulkDelete(array $uuid)
  {
    try {
      $payments = $this->mainRepository->getWhere([
        'uuid' => [
          'operator' => WhereOperator::In->value,
          'value' => $uuid
        ]
      ]);

      $deleted = 0;

      foreach ($payments as $payment) {
        $payment->delete();
        $deleted++;
      }

      return $this->setMessage("Berhasil menghapus {$deleted} Data {$this->title}")->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }
}
