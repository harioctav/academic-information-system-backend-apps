<?php

namespace App\Http\Requests\Finances;

use Illuminate\Foundation\Http\FormRequest;

class PaymentStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'student_uuid' => ['required', 'uuid', 'exists:students,uuid'],
            'billing_uuid' => ['required', 'uuid', 'exists:billings,uuid'],
            'invoice_uuid' => ['required', 'uuid', 'exists:invoices,uuid'],
            'payment_method' => ['required', 'string'],
            'payment_plan' => ['required', 'string'],
            'payment_date' => ['required', 'date'],
            'amount_paid' => ['required', 'numeric', 'min:1'],
            'transfer_to' => ['nullable', 'string'],
            'payment_status' => ['required', 'in:pending,paid,failed'],
            'note' => ['nullable', 'string'],
            'proof_of_payment_base64' => ['required', 'string', 'regex:/^data:image\/(png|jpg|jpeg|gif|webp);base64,/'],
        ];
    }
}
