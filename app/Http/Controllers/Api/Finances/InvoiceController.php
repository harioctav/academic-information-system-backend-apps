<?php
// app/Http/Controllers/Api/Finances/InvoiceController.php
// app/Http/Controllers/Api/Finances/InvoiceController.php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('student')->latest()->get();
        return response()->json($invoices);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nim' => 'required|string',
            'payment_method' => 'nullable|string',
            'bank_fee' => 'nullable|numeric',
            'subscription_fee' => 'nullable|numeric',
            'subscription_code' => 'nullable|string',
            'billing_code' => 'nullable|string',
        ]);

        $student = Student::where('nim', $request->nim)->first();

        if (!$student) {
            return response()->json(['error' => 'Student with given NIM not found.'], 404);
        }

        $total_fee = ($request->bank_fee ?? 0) + ($request->subscription_fee ?? 0);

        $invoice = Invoice::create([
            'student_id' => $student->id,
            'payment_method' => $request->payment_method,
            'bank_fee' => $request->bank_fee ?? 0,
            'subscription_fee' => $request->subscription_fee ?? 0,
            'subscription_code' => $request->subscription_code,
            'total_fee' => $total_fee,
            'billing_code' => $request->billing_code,
            'payment_status' => 'unpaid',
        ]);

        return response()->json($invoice, 201);
    }

    public function show($id)
    {
        $invoice = Invoice::with('student')->findOrFail($id);
        return response()->json($invoice);
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $request->validate([
            'payment_status' => 'required|string|in:unpaid,paid,canceled',
        ]);

        $invoice->update([
            'payment_status' => $request->payment_status,
        ]);

        return response()->json($invoice);
    }
}
