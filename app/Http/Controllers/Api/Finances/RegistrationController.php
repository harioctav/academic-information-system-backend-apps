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

  # ==================== PUBIC ==================== #
  public function showBatch($uuid): JsonResponse
  {
    $batch = $this->registrationService->getBatchByUuid($uuid);

    if (isset($batch['error'])) {
      return response()->json($batch, $batch['code']);
    }

    return response()->json([
      'success' => true,
      'data' => $batch
    ]);
  }

  public function showStudent($nim): JsonResponse
  {
    $student = $this->registrationService->getStudentByNim($nim);

    if (!$student) {
      return response()->json([
        'success' => false,
        'message' => 'Mahasiswa tidak ditemukan.'
      ], 404);
    }

    return response()->json([
      'success' => true,
      'data' => $student
    ]);
  }


  public function submit(RegistrationMhsRequest $request): JsonResponse
  {
    return $this->registrationService->handleRegistration($request);
  }

  # ==================== FINANCE ==================== #
  public function index(Request $request)
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
        'payment_system',
        'program_type',
        'semester',
      ]
    );

    $perPage = $request->input('per_page', 10);
    $result = $query->latest();

    return RegistrationResource::collection(
      $perPage == -1 ? $result->get() : $result->paginate($perPage)
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
    return $registration = $this->registrationService->handleUpdate($request, $registration);
  }

  public function destroy(Registration $registration): JsonResponse
  {
    return $this->registrationService->handleDelete($registration);
  }

  /**
   * Remove multiple resources from storage.
   */
  public function bulkDestroy(Request $request): JsonResponse
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:registrations,uuid'
    ]);

    return $this->registrationService->handleBulkDelete($request->ids);
  }
}
