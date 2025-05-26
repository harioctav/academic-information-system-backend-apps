<?php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finances\PaymentStoreRequest;
use App\Http\Requests\Finances\PaymentUpdateRequest;
use App\Http\Resources\Finances\PaymentResource;
use App\Services\Payment\PaymentService;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $payments = $this->paymentService->query()->with(['student', 'billing'])->latest()->get();
        return PaymentResource::collection($payments);
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
}
