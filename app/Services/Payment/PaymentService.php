<?php

namespace App\Services\Payment;

use LaravelEasyRepository\BaseService;

interface PaymentService extends BaseService
{
    public function getAllPaginated($perPage);
    public function showById($id);
    public function handleStore($request);
    public function handleUpdate($request, $id);
    public function handleDelete($id);
}
