<?php

namespace App\Repositories\Payment;

use LaravelEasyRepository\Repository;

interface PaymentRepository extends Repository
{
    public function getWithRelationsPaginated($perPage);
    public function findWithRelations($id);
}
