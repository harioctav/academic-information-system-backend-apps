<?php

namespace App\Repositories\Invoice;

use LaravelEasyRepository\Repository;

interface InvoiceRepository extends Repository
{
    public function getWhere(
        $wheres = [],
        $columns = '*',
        $comparisons = '=',
        $orderBy = null,
        $orderByType = null
    );
}
