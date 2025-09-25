<?php

namespace App\Services\Payment;

use App\Models\Payment;
use LaravelEasyRepository\BaseService;

interface PaymentService extends BaseService
{
    public function query();
    public function handleStore(array $data): Payment;
    public function handleShow(string $uuid): Payment;
    public function handleUpdate(array $data, string $uuid): Payment;
}
