<?php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finances\RegistrationRequest;
use App\Http\Resources\Finances\RegistrationResource;
use App\Models\Registration;
use App\Services\Registration\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    protected RegistrationService $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $query = Registration::with(['student', 'address'])->latest();
        $registrations = $perPage == -1 ? $query->get() : $query->paginate($perPage);

        return response()->json(
            RegistrationResource::collection($registrations)
        );
    }

    public function store(RegistrationRequest $request): JsonResponse
    {
        $registration = $this->registrationService->handleStore($request);

        return response()->json([
            'message' => 'Registration created successfully.',
            'data' => new RegistrationResource($registration)
        ], 201);
    }

    public function show(Registration $registration): RegistrationResource
    {
        $registration->load(['student', 'address']);
        return new RegistrationResource($registration);
    }

    public function update(RegistrationRequest $request, Registration $registration): JsonResponse
    {
        $registration = $this->registrationService->handleUpdate($request, $registration);

        return response()->json([
            'message' => 'Registration updated successfully.',
            'data' => new RegistrationResource($registration)
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
