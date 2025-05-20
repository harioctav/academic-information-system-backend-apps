<?php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $payments = $this->paymentService->getAllPaginated(10);
        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'registration_id' => 'nullable|exists:registrations,id',
            'billing_id' => 'nullable|exists:billings,id',
            'payment_type' => 'required|string',
            'payment_method' => 'required|string',
            'payment_status' => 'required|string',
            'payment_date' => 'required|date',
            'amount_paid' => 'required|numeric',
            'proof_of_payment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $request->merge($validator->validated());

        $payment = $this->paymentService->handleStore($request);

        return response()->json([
            'message' => 'Payment recorded successfully.',
            'data' => $payment,
        ], 201);
    }

    public function show($id)
    {
        $payment = $this->paymentService->showById($id);
        return response()->json($payment);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_type' => 'sometimes|string',
            'payment_method' => 'sometimes|string',
            'payment_status' => 'sometimes|string',
            'payment_date' => 'sometimes|date',
            'amount_paid' => 'sometimes|numeric',
            'proof_of_payment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $request->merge($validator->validated());

        $payment = $this->paymentService->handleUpdate($request, $id);

        return response()->json([
            'message' => 'Payment updated successfully.',
            'data' => $payment,
        ]);
    }

    public function destroy($id)
    {
        $this->paymentService->handleDelete($id);

        return response()->json([
            'message' => 'Payment deleted successfully.',
        ]);
    }
}
