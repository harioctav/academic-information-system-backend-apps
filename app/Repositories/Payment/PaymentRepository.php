<?php

namespace App\Repositories\Payment;

use LaravelEasyRepository\Repository;

interface PaymentRepository extends Repository
{
    public function query();
    public function getWithRelationsPaginated($perPage);
    public function findWithRelations($id);
}
