<?php

namespace App\Repositories\Billing;

use LaravelEasyRepository\Repository;

interface BillingRepository extends Repository
{
    public function getWhere(
        $wheres = [],
        $columns = '*',
        $comparisons = '=',
        $orderBy = null,
        $orderByType = null
    );
}
