<?php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
use App\Helpers\SearchHelper;
use App\Http\Requests\Finances\RegistrationBatchRequest;
use App\Http\Resources\Finances\RegistrationBatchResource;
use App\Services\RegistrationBatch\RegistrationBatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\RegistrationBatch;

class RegistrationBatchController extends Controller
{
    protected RegistrationBatchService $registrationBatchService;

    // public function __construct(RegistrationBatchService $registrationBatchService)
    // {
    //     $this->registrationBatchService = $registrationBatchService;
    // }

    public function index(Request $request)
    {
        $query = SearchHelper::applySearchQuery(
            query: RegistrationBatch::query(),
            request: $request,
            searchableFields: [
                'name',
                'description',
                'notes'
            ],
            sortableFields: [
                'name',
                'start_date',
                'end_date',
                'created_at'
            ],
            filterFields: []
        );

        $perPage = $request->input('per_page', 10);
        $result = $query->latest();

        return RegistrationBatchResource::collection(
            $perPage == -1 ? $result->get() : $result->paginate($perPage)
        );
    }

    public function store(RegistrationBatchRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $registrationBatch = RegistrationBatch::create($validatedData);

        return response()->json([
            'message' => 'RegistrationBatch created successfully.',
            'data' => new RegistrationBatchResource($registrationBatch),
        ], 201);
    }

    public function show(RegistrationBatch $registrationBatch): RegistrationBatchResource
    {
        return new RegistrationBatchResource($registrationBatch);
    }

    public function update(RegistrationBatchRequest $request, RegistrationBatch $registrationBatch): JsonResponse
    {
        $registrationBatch = $this->registrationBatchService->handleUpdate($request, $registrationBatch);

        return response()->json([
            'message' => 'RegistrationBatch updated successfully.',
            'data' => new RegistrationBatchResource($registrationBatch),
        ]);
    }

    public function destroy(RegistrationBatch $registrationBatch): JsonResponse
    {
        $this->registrationBatchService->handleDelete($registrationBatch);

        return response()->json([
            'message' => 'RegistrationBatch deleted successfully.'
        ]);
    }
}
