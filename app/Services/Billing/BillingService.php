<?php

namespace App\Services\Billing;

use App\Models\Billing;
use LaravelEasyRepository\BaseService;

interface BillingService extends BaseService
{
    public function query();
    public function getWhere(
        $wheres = [],
        $columns = '*',
        $comparisons = '=',
        $orderBy = null,
        $orderByType = null
    );
    public function handleStore($request);
    public function handleUpdate($request, Billing $billing);
    public function handleDelete(Billing $billing);
    public function handleBulkDelete(array $uuid);
}
