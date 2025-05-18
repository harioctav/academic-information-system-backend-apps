<?php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    public function index()
    {
        $billings = Billing::with('student')->latest()->paginate(10);
        return response()->json($billings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'billing_code' => 'required|string|unique:billings,billing_code',
            'bank_fee' => 'required|numeric',
            'salut_member_fee' => 'required|numeric',
            'semester_fee' => 'required|numeric',
            'total_fee' => 'required|numeric',
            'payment_method' => 'required|string',
            'payment_status' => 'required|string',
            'note' => 'nullable|string',
        ]);

        $validated['uuid'] = Str::uuid();

        $billing = Billing::create($validated);

        return response()->json([
            'message' => 'Billing created successfully.',
            'data' => $billing,
        ], 201);
    }

    public function show($id)
    {
        $billing = Billing::with('student')->findOrFail($id);
        return response()->json($billing);
    }

    public function update(Request $request, $id)
    {
        $billing = Billing::findOrFail($id);

        $validated = $request->validate([
            'billing_code' => 'sometimes|string|unique:billings,billing_code,' . $billing->id,
            'bank_fee' => 'sometimes|numeric',
            'salut_member_fee' => 'sometimes|numeric',
            'semester_fee' => 'sometimes|numeric',
            'total_fee' => 'sometimes|numeric',
            'payment_method' => 'sometimes|string',
            'payment_status' => 'sometimes|string',
            'note' => 'nullable|string',
        ]);

        $billing->update($validated);

        return response()->json([
            'message' => 'Billing updated successfully.',
            'data' => $billing,
        ]);
    }

    public function destroy($id)
    {
        $billing = Billing::findOrFail($id);
        $billing->delete();

        return response()->json([
            'message' => 'Billing deleted successfully.'
        ]);
    }
}
