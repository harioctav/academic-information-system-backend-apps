<?php

namespace App\Services\Payment;

use App\Models\Payment;
use LaravelEasyRepository\BaseService;

interface PaymentService extends BaseService
{
    public function query();
    public function handleStore($data);
    public function handleShow(string $uuid);
    public function handleUpdate(String $status, Payment $payment);
}
