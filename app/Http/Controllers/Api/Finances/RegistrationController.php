<?php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
use App\Helpers\SearchHelper;
use App\Http\Requests\Finances\RegistrationRequest;
use App\Http\Requests\Finances\RegistrationMhsRequest;
use App\Http\Resources\Finances\RegistrationResource;
use App\Services\Registration\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\Registration;
use App\Models\RegistrationBatch;
use App\Models\Student;

class RegistrationController extends Controller
{
    protected RegistrationService $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    public function checkRegistrationBatch($uuid): JsonResponse
    {
        $batch = RegistrationBatch::where('uuid', $uuid)->first();

        if (!$batch) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendaftaran tidak ditemukan.'
            ], 404);
        }

        // Pastikan start_date dan end_date adalah instance Carbon
        $startDate = Carbon::parse($batch->start_date);
        $endDate = Carbon::parse($batch->end_date);
        $today = Carbon::today();

        if ($today->lt($startDate)) {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftaran belum dibuka.',
                'start_date' => $startDate->format('d-m-Y'),
            ], 403);
        }

        if ($today->gt($endDate)) {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftaran telah ditutup.',
                'end_date' => $endDate->format('d-m-Y'),
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'uuid' => $batch->uuid,
                'name' => $batch->name,
                'description' => $batch->description,
                'notes' => $batch->notes,
                'start_date' => $startDate->format('d-m-Y'),
                'end_date' => $endDate->format('d-m-Y'),
                'created_at' => Carbon::parse($batch->created_at)->format('d-m-Y H:i:s'),
                'updated_at' => Carbon::parse($batch->updated_at)->format('d-m-Y H:i:s'),
            ]
        ]);
    }

    public function getMahasiswaByNim($nim)
    {
        $student = Student::with('addresses')
            ->where('nim', $nim)
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'nim' => $student->nim,
            'name' => $student->name,
            'phone' => $student->phone,
            'address_line' => ($student->province?->name ?? '') . ' ' .
                ($student->regency?->name ?? '') . ' ' .
                ($student->district?->name ?? '') . ' ' .
                ($student->village?->name ?? '') . ' ' .
                (optional($student->domicileAddress)->address ?? ''),
            'address' => [
                'province' => $student->province?->name ?? null,
                'regency'  => $student->regency?->name ?? null,
                'district' => $student->district?->name ?? null,
                'village'  => $student->village?->name ?? null,
                'detail'   => optional($student->domicileAddress)->address ?? null,
            ],
        ]);
    }

    public function postRegistration(RegistrationMhsRequest $request): JsonResponse
    {
        return $this->registrationService->handleRegistration($request);
    }

    public function index(Request $request): JsonResponse
    {
        $query = SearchHelper::applySearchQuery(
            query: Registration::with(['student', 'registrationBatch']),
            request: $request,
            searchableFields: [
                'registration_number',
                'student.name',
                'student.nim',
                'registrationBatch.name'
            ],
            sortableFields: [
                'registration_number',
                'created_at',
                'updated_at',
            ],
            filterFields: [
                'student_category',
                'payment_method',
                'program_type',
                'semester',
            ]
        );

        $perPage = $request->input('per_page', 10);
        $result = $query->latest();

        return response()->json(
            RegistrationResource::collection(
                $perPage == -1 ? $result->get() : $result->paginate($perPage)
            )
        );
    }


    public function store(RegistrationRequest $request): JsonResponse
    {
        return $this->registrationService->handleStore($request);
    }

    public function show(Registration $registration): RegistrationResource
    {
        $registration->load(['student', 'address', 'registrationBatch']);
        return new RegistrationResource($registration);
    }

    public function update(RegistrationRequest $request, Registration $registration): JsonResponse
    {
        $registration = $this->registrationService->handleUpdate($request, $registration);

        return response()->json([
            'message' => 'Registration updated successfully.',
            'data' => new RegistrationResource($registration),
        ]);
    }


    public function destroy(Registration $registration): JsonResponse
    {
        $this->registrationService->handleDelete($registration);

        return response()->json([
            'message' => 'Registration deleted successfully.'
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:registrations,uuid',
        ]);

        $this->registrationService->handleBulkDelete($request->ids);

        return response()->json([
            'message' => 'Registrations deleted successfully.'
        ]);
    }
}
