<?php

namespace App\Services\RegistrationBatch;

interface RegistrationBatchService
{
    public function store(array $data);
    public function updateByUuid(string $uuid, array $data);
    public function deleteByUuid(string $uuid);
}
