<?php

namespace App\Repositories\Registration;

use LaravelEasyRepository\Repository;

interface RegistrationRepository extends Repository
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
