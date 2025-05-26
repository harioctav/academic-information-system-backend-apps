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
            'registration_batch_id' => ($isUpdate ? 'sometimes' : 'required') . '|exists:registration_batches,id',
            'student_id' => ($isUpdate ? 'sometimes' : 'required') . '|exists:students,id',
            'shipping_address' => ($isUpdate ? 'sometimes' : 'required') . '|string',
            'student_category' => ($isUpdate ? 'sometimes' : 'required') . '|string',
            'payment_system' => ($isUpdate ? 'sometimes' : 'required') . '|string',
            'program_type' => ($isUpdate ? 'sometimes' : 'required') . '|string',
            'tutorial_service' => ($isUpdate ? 'sometimes' : 'required') . '|boolean',
            'semester' => ($isUpdate ? 'sometimes' : 'required') . '|string',
            'interested_spp' => ($isUpdate ? 'sometimes' : 'required') . '|boolean',
        ];
    }
}
