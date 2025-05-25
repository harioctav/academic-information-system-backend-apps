<?php

namespace App\Services\RegistrationBatch;

use Illuminate\Support\Str;
use App\Repositories\RegistrationBatch\RegistrationBatchRepository;

class RegistrationBatchServiceImplement implements RegistrationBatchService
{
    protected $repository;

    public function __construct(RegistrationBatchRepository $repository)
    {
        $this->repository = $repository;
    }

    public function store(array $data)
    {
        $data['uuid'] = Str::uuid();
        return $this->repository->create($data);
    }

    public function updateByUuid(string $uuid, array $data)
    {
        $batch = $this->repository->findByUuid($uuid);
        if ($batch) {
            $batch->update($data);
            return $batch;
        }
        return null;
    }

    public function deleteByUuid(string $uuid)
    {
        $batch = $this->repository->findByUuid($uuid);
        return $batch ? $batch->delete() : false;
    }
}
