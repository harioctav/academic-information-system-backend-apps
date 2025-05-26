<?php

namespace App\Repositories\Invoice;

use LaravelEasyRepository\Repository;

interface InvoiceRepository extends Repository
{
    public function query();
    public function getWhere(
        $wheres = [],
        $columns = '*',
        $comparisons = '=',
        $orderBy = null,
        $orderByType = null
    );
}
