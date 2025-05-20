<?php

namespace App\Services\Registration;

use App\Models\Registration;
use LaravelEasyRepository\BaseService;

interface RegistrationService extends BaseService
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
    public function handleUpdate($request, Registration $registration);
    public function handleDelete(Registration $registration);
    public function handleBulkDelete(array $uuid);
}
