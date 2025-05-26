<?php

namespace App\Http\Requests\Finances;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'billing_id' => 'required|exists:billings,id',
            'total_amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'payment_status' => 'required|in:unpaid,paid,canceled',
            'payment_method' => 'nullable|string',
            'payment_type' => 'nullable|string',
            'note' => 'nullable|string',
            'details' => 'nullable|array',
            'details.*.item_name' => 'required_with:details|string',
            'details.*.item_type' => 'nullable|string',
            'details.*.amount' => 'required_with:details|numeric|min:0',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
