<?php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
use App\Helpers\SearchHelper;
use App\Http\Requests\Finances\PaymentStoreRequest;
use App\Http\Requests\Finances\PaymentUpdateRequest;
use App\Http\Resources\Finances\PaymentResource;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;


class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $query = SearchHelper::applySearchQuery(
            query: $this->paymentService->query()->with(['student', 'billing']),
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
                'payment_type',
                'status',
                'semester',
            ]
        );

        $perPage = $request->input('per_page', 10);
        $result = $query->latest();

        return PaymentResource::collection(
            $perPage == -1 ? $result->get() : $result->paginate($perPage)
        );
    }

    public function store(PaymentStoreRequest $request)
    {
        $payment = $this->paymentService->handleStore($request->validated());
        return new PaymentResource($payment);
    }

    public function show(string $uuid)
    {
        $payment = $this->paymentService->handleShow($uuid);
        return new PaymentResource($payment);
    }

    public function update(PaymentUpdateRequest $request, string $uuid)
    {
        $payment = $this->paymentService->handleUpdate($request->validated(), $uuid);
        return new PaymentResource($payment);
    }

    public function submit(PaymentStoreRequest $request)
    {
        $payment = $this->paymentService->handleSubmit($request->validated());
        return new PaymentResource($payment);
        // return new PaymentResource($payment->load('student')); 
    }
}
