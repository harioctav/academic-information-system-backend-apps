<?php

namespace App\Services\Payment;

use App\Models\Payment;
use LaravelEasyRepository\BaseService;

interface PaymentService extends BaseService
{
    public function query();
    public function getWhere(
        $wheres = [],
        $columns = '*',
        $comparisons = '=',
        $orderBy = null,
        $orderByType = null
    );
    public function handleStore(array $data): Payment;
    public function handleShow(string $uuid): Payment;
    public function handleUpdate(array $data, string $uuid): Payment;
    public function handleSubmit(array $data): Payment;
}
