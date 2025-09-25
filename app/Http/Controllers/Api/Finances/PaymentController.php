<?php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finances\PaymentRequest;
use App\Http\Resources\Finances\PaymentResource;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $payments = $this->paymentService->query()->with(['student', 'billing'])->latest()->get();
        return PaymentResource::collection($payments);
    }

    public function store(PaymentRequest $request): JsonResponse
    {
        return $this->paymentService->handleStore($request);
    }

    public function show(string $uuid)
    {
        $payment = $this->paymentService->handleShow($uuid);
        return new PaymentResource($payment);
    }

    // public function update(PaymentUpdateRequest $request, string $uuid)
    // {
    //     $payment = $this->paymentService->handleUpdate($request->validated(), $uuid);
    //     return new PaymentResource($payment);
    // }
}
