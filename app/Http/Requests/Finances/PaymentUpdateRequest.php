<?php

namespace App\Http\Requests\Finances;

use Illuminate\Foundation\Http\FormRequest;

class PaymentStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'billing_id' => 'nullable|exists:billings,id',
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

class PaymentUpdateRequest extends PaymentStoreRequest {}
