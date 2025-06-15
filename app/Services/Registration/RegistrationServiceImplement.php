<?php

namespace App\Services\Registration;

use App\Enums\WhereOperator;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Registration\RegistrationRepository;
use App\Http\Resources\Finances\RegistrationResource;
use App\Models\Student;
use App\Models\Registration;
use App\Models\RegistrationBatch;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
                $registration->load(['student']);

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
                $result->load(['student']);

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

    public function getStudentByNim(string $nim): ?array
    {
        $student = Student::with('addresses')
            ->where('nim', $nim)
            ->first();

        if (!$student) return null;

        return [
            'nim'   => $student->nim,
            'name'  => $student->name,
            'phone' => $student->phone,
            'address_line' => ($student->province?->name ?? '') . ' ' .
                ($student->regency?->name ?? '') . ' ' .
                ($student->district?->name ?? '') . ' ' .
                ($student->village?->name ?? '') . ' ' .
                (optional($student->domicileAddress)->address ?? ''),
            'address' => [
                'province' => $student->province?->name,
                'regency'  => $student->regency?->name,
                'district' => $student->district?->name,
                'village'  => $student->village?->name,
                'detail'   => optional($student->domicileAddress)->address,
            ],
        ];
    }

    public function getBatchByUuid(string $uuid): array
    {
        $batch = RegistrationBatch::where('uuid', $uuid)->first();

        if (!$batch) {
            return [
                'error' => true,
                'message' => 'Data pendaftaran tidak ditemukan.',
                'code' => 404,
            ];
        }

        $startDate = Carbon::parse($batch->start_date);
        $endDate   = Carbon::parse($batch->end_date);
        $today     = Carbon::today();

        if ($today->lt($startDate)) {
            return [
                'error' => true,
                'message' => 'Pendaftaran belum dibuka.',
                'start_date' => $startDate->format('d-m-Y'),
                'code' => 403,
            ];
        }

        if ($today->gt($endDate)) {
            return [
                'error' => true,
                'message' => 'Pendaftaran telah ditutup.',
                'end_date' => $endDate->format('d-m-Y'),
                'code' => 403,
            ];
        }

        return [
            'uuid'        => $batch->uuid,
            'name'        => $batch->name,
            'description' => $batch->description,
            'notes'       => $batch->notes,
            'start_date'  => $startDate->format('d-m-Y'),
            'end_date'    => $endDate->format('d-m-Y'),
            'created_at'  => Carbon::parse($batch->created_at)->format('d-m-Y H:i:s'),
            'updated_at'  => Carbon::parse($batch->updated_at)->format('d-m-Y H:i:s'),
        ];
    }
}
