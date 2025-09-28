<?php

namespace App\Http\Controllers\Api\Finances;

use App\Models\Payment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finances\PaymentRequest;
use App\Http\Requests\Finances\PaymentStatusRequest;
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

    public function show(Payment $payment)
    {
        $payment = $this->paymentService->handleShow($payment->uuid);
        return new PaymentResource($payment);
    }

    public function update(PaymentStatusRequest $request, Payment $payment) : JsonResponse
    {
        return $this->paymentService->handleUpdate($request->input('payment_status'), $payment);
    }
}
