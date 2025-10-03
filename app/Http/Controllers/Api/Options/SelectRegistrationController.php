<?php

namespace App\Http\Controllers\Api\Options;

use App\Helpers\SearchHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Finances\RegistrationResource;
use App\Models\Registration;
use App\Services\Registration\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SelectRegistrationController extends Controller
{
  public function __construct(
    protected RegistrationService $registrationService
  ) {
    # code...
  }

  public function registrations(Request $request)
  {
    $query = $this->registrationService->query();

    // Filter by student_id if provided
    if ($request->has('student_id') && $request->student_id) {
      $query->where('student_id', $request->student_id);
    }

    $query = SearchHelper::applySearchQuery(
      query: $query,
      request: $request,
      searchableFields: [
        'registration_number',
        'student_category',
        'payment_system',
        'program_type',
        'tutorial_service',
        'semester'
      ],
      sortableFields: [
        'registration_number',
        'student_category',
        'payment_system',
        'program_type',
        'tutorial_service',
        'semester',
        'created_at',
        'updated_at'
      ],
      filterFields: [
        'student_id',
        'registration_batch_id',
        'student_category',
        'payment_system',
        'program_type',
        'tutorial_service',
        'semester'
      ]
    );

    return RegistrationResource::collection(
      $query->with(['student', 'registrationBatch'])->latest()->paginate($request->input('per_page', 10))
    );
  }

  public function registration(Registration $registration)
  {
    $registration->load(['student', 'address', 'registrationBatch']);
    return new RegistrationResource($registration);
  }
}
