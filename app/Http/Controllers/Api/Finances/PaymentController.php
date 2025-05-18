<?php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['student', 'registration', 'billing'])->latest()->paginate(10);
        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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

        $validated['uuid'] = Str::uuid();

        if ($request->hasFile('proof_of_payment')) {
            $validated['proof_of_payment'] = $request->file('proof_of_payment')->store('payment_proofs', 'public');
        }

        $payment = Payment::create($validated);

        return response()->json([
            'message' => 'Payment recorded successfully.',
            'data' => $payment,
        ], 201);
    }

    public function show($id)
    {
        $payment = Payment::with(['student', 'registration', 'billing'])->findOrFail($id);
        return response()->json($payment);
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'payment_type' => 'sometimes|string',
            'payment_method' => 'sometimes|string',
            'payment_status' => 'sometimes|string',
            'payment_date' => 'sometimes|date',
            'amount_paid' => 'sometimes|numeric',
            'proof_of_payment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('proof_of_payment')) {
            $validated['proof_of_payment'] = $request->file('proof_of_payment')->store('payment_proofs', 'public');
        }

        $payment->update($validated);

        return response()->json([
            'message' => 'Payment updated successfully.',
            'data' => $payment,
        ]);
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully.',
        ]);
    }
}
