<?php

namespace App\Services\Invoice;

use App\Models\Invoice;
use LaravelEasyRepository\BaseService;

interface InvoiceService extends BaseService
{
    public function query();
    public function getWhere(
        array $wheres = [],
        $columns = '*',
        $comparisons = '=',
        $orderBy = null,
        $orderByType = null
    );
    public function handleStore(array $data): Invoice;
    public function handleUpdate(array $data, Invoice $invoice): Invoice;
    public function handleShow(string $uuid): Invoice;
    public function handleShowByBilling(string $billingUuid);
    public function handleShowByNim(string $nim);
}
