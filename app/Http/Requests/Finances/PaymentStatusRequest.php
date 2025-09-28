<?php

namespace App\Http\Requests\Finances;

use Illuminate\Foundation\Http\FormRequest;

class PaymentStatusRequest extends FormRequest
{
    /**
   * Determine if the user is authorized to make this request.
   */
    public function authorize()
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_status' => 'required|in:pending,confirmed,rejected',
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => ':attribute harus tidak boleh dikosongkan',
            '*.exists' => ':attribute tidak ditemukan di database',
            '*.in' => ':attribute tidak valid',
            '*.numeric' => ':attribute harus berupa angka',
            '*.string' => ':attribute harus berupa string',
            '*.date' => ':attribute harus berupa tanggal yang valid',
            '*.min' => ':attribute minimal :min karakter atau lebih',
        ];
    }

    public function attributes(): array
    {
        return [
            'payment_status' => 'Status Pembayaran',
        ];
    }
}
