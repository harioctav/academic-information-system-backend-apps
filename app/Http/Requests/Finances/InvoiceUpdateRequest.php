<?php

namespace App\Http\Requests\Finances;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return (new InvoiceStoreRequest())->rules();
    }

    public function authorize(): bool
    {
        return true;
    }
}
