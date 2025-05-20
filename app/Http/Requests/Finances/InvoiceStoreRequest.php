<?php

namespace App\Http\Requests\Finances;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ubah sesuai kebutuhan autentikasi/otorisasi
    }

    public function rules(): array
    {
        return [
            'nim' => 'required|string',
            'payment_method' => 'nullable|string',
            'bank_fee' => 'nullable|numeric',
            'subscription_fee' => 'nullable|numeric',
            'subscription_code' => 'nullable|string',
            'billing_code' => 'nullable|string',
        ];
    }
}
