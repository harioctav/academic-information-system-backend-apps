<?php

namespace App\Services\Registration;

use App\Enums\WhereOperator;
use App\Models\Registration;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Registration\RegistrationRepository;
use App\Http\Resources\Finances\RegistrationResource;

class RegistrationServiceImplement extends ServiceApi implements RegistrationService
{
    protected string $title = "Pendaftaran";

    protected string $create_message = "Data Pendaftaran berhasil dibuat";
    protected string $update_message = "Data Pendaftaran berhasil diperbarui";
    protected string $delete_message = "Data Pendaftaran berhasil dihapus";

    protected RegistrationRepository $mainRepository;

    public function __construct(RegistrationRepository $mainRepository)
    {
        $this->mainRepository = $mainRepository;
    }

    public function query()
    {
        return $this->mainRepository->query();
    }

    public function getWhere(
        $wheres = [],
        $columns = '*',
        $comparisons = '=',
        $orderBy = null,
        $orderByType = null
    ) {
        return $this->mainRepository->getWhere(
            wheres: $wheres,
            columns: $columns,
            comparisons: $comparisons,
            orderBy: $orderBy,
            orderByType: $orderByType
        );
    }

    public function handleStore($request)
    {
        try {
            $payload = $request->validated();
            $result = $this->mainRepository->create($payload);

            return $this->setMessage($this->create_message)
                ->setData(new RegistrationResource($result))
                ->toJson();
        } catch (\Exception $e) {
            return $this->setMessage($e->getMessage())->toJson();
        }
    }

    public function handleUpdate($request, Registration $registration)
    {
        try {
            $registration->update($request->validated());

            return $this->setMessage($this->update_message)
                ->setData(new RegistrationResource($registration))
                ->toJson();
        } catch (\Exception $e) {
            return $this->setMessage($e->getMessage())->toJson();
        }
    }

    public function handleDelete(Registration $registration)
    {
        try {
            $registration->delete();

            return $this->setMessage($this->delete_message)->toJson();
        } catch (\Exception $e) {
            return $this->setMessage($e->getMessage())->toJson();
        }
    }

    public function handleBulkDelete(array $uuid)
    {
        try {
            $registrations = $this->getWhere([
                'uuid' => [
                    'operator' => WhereOperator::In->value,
                    'value' => $uuid
                ]
            ])->get();

            $deleted = 0;

            foreach ($registrations as $registration) {
                $registration->delete();
                $deleted++;
            }

            return $this->setMessage("Berhasil menghapus {$deleted} Data {$this->title}")->toJson();
        } catch (\Exception $e) {
            return $this->setMessage($e->getMessage())->toJson();
        }
    }
}
