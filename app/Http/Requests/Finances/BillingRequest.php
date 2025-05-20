<?php

namespace App\Http\Requests\Finances;

use Illuminate\Foundation\Http\FormRequest;

class BillingRequest extends FormRequest
{
    public function rules(): array
    {
        $billingId = $this->route('billing')?->id;

        return [
            'student_id' => 'required|exists:students,id',
            'billing_code' => 'required|string|unique:billings,billing_code' . ($billingId ? ',' . $billingId : ''),
            'bank_fee' => 'required|numeric',
            'salut_member_fee' => 'required|numeric',
            'semester_fee' => 'required|numeric',
            'total_fee' => 'required|numeric',
            'payment_method' => 'required|string',
            'payment_status' => 'required|string',
            'note' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
