<?php

namespace App\Services\Registration;

use App\Enums\WhereOperator;
use App\Models\Registration;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Registration\RegistrationRepository;
use App\Http\Resources\Finances\RegistrationResource;
use App\Models\Student;
use App\Models\RegistrationBatch;
use Illuminate\Support\Str;

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
            $payload['registration_number'] = $this->generateRegistrationNumber();
            $result = $this->mainRepository->create($payload);


            return $this->setMessage($this->create_message)
                ->setData(new RegistrationResource($result))
                ->toJson();
        } catch (\Exception $e) {
            return $this->setMessage($e->getMessage())->toJson();
        }
    }

    public function handleRegistration($request)
    {
        try {
            $payload = $request->validated();

            $student = Student::where('nim', $payload['nim'])->first();
            if (!$student) {
                return $this->setMessage("Student with NIM {$payload['nim']} not found")->toJson();
            }

            $batch = RegistrationBatch::where('uuid', $payload['registration_batch_uuid'])->first();
            if (!$batch) {
                return $this->setMessage("Registration batch not found")->toJson();
            }

            $payload['student_id'] = $student->id;
            $payload['registration_batch_id'] = $batch->id;

            $isUpdate = $payload['is_update'] ?? false;

            unset($payload['nim'], $payload['registration_batch_uuid'], $payload['is_update']);

            if ($isUpdate) {
                $registration = Registration::where('student_id', $student->id)
                    ->where('registration_batch_id', $batch->id)
                    ->first();

                if (!$registration) {
                    return $this->setMessage("Registration data not found for update")->toJson();
                }

                // Update data registrasi
                $registration->update($payload);

                return $this->setMessage("Registration updated successfully")
                    ->setData(new RegistrationResource($registration))
                    ->toJson();
            } else {
                // Cek dulu apakah sudah ada registrasi dengan student_id & batch_id
                $exists = Registration::where('student_id', $student->id)
                    ->where('registration_batch_id', $batch->id)
                    ->exists();

                if ($exists) {
                    return $this->setMessage("Anda sudah registrasi")->toJson();
                }

                // Kalau belum ada, lanjut insert data baru
                // generate registration_number, uuid, insert...
                $payload['registration_number'] = $this->generateRegistrationNumber();
                $payload['uuid'] = Str::uuid();

                $result = $this->mainRepository->create($payload);

                return $this->setMessage($this->create_message)
                    ->setData(new RegistrationResource($result))
                    ->toJson();
            }
        } catch (\Exception $e) {
            return $this->setMessage($e->getMessage())->toJson();
        }
    }


    public function handleUpdate($request, Registration $registration): Registration
    {
        $registration->update($request->validated());

        return $registration; 
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

    private function generateRegistrationNumber(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');

        $lastRegistration = Registration::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotNull('registration_number')
            ->orderByDesc('registration_number')
            ->first();

        $lastNumber = 0;
        if ($lastRegistration && preg_match("/^REG-{$year}-{$month}-(\d{4})$/", $lastRegistration->registration_number, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        return "REG-{$year}-{$month}-{$nextNumber}";
    }
}
