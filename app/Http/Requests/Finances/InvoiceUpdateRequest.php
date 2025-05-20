<?php

namespace App\Http\Requests\Finances;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_status' => 'required|string|in:unpaid,paid,canceled',
        ];
    }
}
