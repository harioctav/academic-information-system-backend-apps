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
    public function handleStore(array $data);
    public function createInvoice($payment);
    public function handleUpdate(array $data, Invoice $invoice);
    public function handleShow(string $uuid);
    public function handleShowByBilling(string $billingUuid);
    public function handleShowByNim(string $nim);
}
