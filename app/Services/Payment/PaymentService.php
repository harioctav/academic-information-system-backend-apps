<?php

namespace App\Services\Payment;

use App\Models\Payment;
use LaravelEasyRepository\BaseService;

interface PaymentService extends BaseService
{
  public function query();
  public function handleStore($data);
  public function handleShow(string $uuid);
  public function handleUpdate($request, Payment $payment);
  public function handleUpdateStatus(String $status, Payment $payment);
  public function handleDelete(Payment $payment);
  public function handleBulkDelete(array $uuid);
}
