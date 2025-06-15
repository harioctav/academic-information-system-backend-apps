<?php

namespace App\Http\Requests\Finances;

use Illuminate\Foundation\Http\FormRequest;

class PaymentStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'student_uuid' => 'required|exists:students,uuid',
            'billing_uuid' => 'nullable|exists:billings,uuid',
            'invoice_uuid' => 'nullable|exists:invoices,uuid',
            'payment_method' => 'required|in:transfer,cash',
            'payment_plan' => 'required|in:cicil,lunas',
            'payment_date' => 'nullable|date',
            'amount_paid' => 'required|numeric|min:0',
            'transfer_to' => 'nullable|string',
            'proof_of_payment' => 'nullable|string',
            'payment_status' => 'required|in:pending,confirmed,rejected',
            'note' => 'nullable|string'
        ];
    }
}
