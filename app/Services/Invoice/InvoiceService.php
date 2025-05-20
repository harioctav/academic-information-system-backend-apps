<?php

namespace App\Services\Invoice;

use App\Models\Invoice;
use LaravelEasyRepository\BaseService;

interface InvoiceService extends BaseService
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
    public function handleUpdate($request, Invoice $invoice);
    public function handleShow($id);
}
