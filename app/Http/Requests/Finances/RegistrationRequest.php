<?php

namespace App\Http\Requests\Finances;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ganti jika perlu otorisasi
    }

    public function rules(): array
    {
        $isUpdate = $this->method() !== 'POST';

        return [
            'student_id' => ($isUpdate ? 'sometimes' : 'required') . '|exists:students,id',
            'address_id' => ($isUpdate ? 'sometimes' : 'required') . '|exists:student_addresses,id',
            'student_category' => ($isUpdate ? 'sometimes' : 'required') . '|string',
            'payment_method' => ($isUpdate ? 'sometimes' : 'required') . '|string',
            'program_type' => ($isUpdate ? 'sometimes' : 'required') . '|string',
            'tutorial_service' => ($isUpdate ? 'sometimes' : 'required') . '|boolean',
            'semester' => ($isUpdate ? 'sometimes' : 'required') . '|string',
            'interested_spp' => ($isUpdate ? 'sometimes' : 'required') . '|boolean',
        ];
    }
}
