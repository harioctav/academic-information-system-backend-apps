<?php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
use App\Helpers\SearchHelper;
use App\Http\Requests\Finances\BillingRequest;
use App\Http\Resources\Finances\BillingResource;
use App\Models\Billing;
use App\Services\Billing\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index(Request $request)
    {
        $query = SearchHelper::applySearchQuery(
            query: Billing::with(['student', 'registration']),
            request: $request,
            searchableFields: [
                'student.name',
                'student.nim',
            ],
            sortableFields: [
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

        return BillingResource::collection(
            $perPage == -1 ? $result->get() : $result->paginate($perPage)
        );
    }

    public function store(BillingRequest $request): JsonResponse
    {
        return $this->billingService->handleStore($request);
    }

    public function show(Billing $billing): BillingResource
    {
        $billing->load(['student']);
        return new BillingResource($billing);
    }

    public function update(BillingRequest $request, Billing $billing): JsonResponse
    {
        return $this->billingService->handleUpdate($request, $billing);
    }

    public function destroy(Billing $billing): JsonResponse
    {
        return $this->billingService->handleDelete($billing);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:billings,uuid'
        ]);

        return $this->billingService->handleBulkDelete($request->ids);
    }
}
