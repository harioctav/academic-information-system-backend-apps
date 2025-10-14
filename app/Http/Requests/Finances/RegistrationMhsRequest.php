<?php

namespace App\Http\Requests\Finances;

use App\Enums\Finances\PaymentSystem;
use App\Enums\Finances\ProgramType;
use App\Enums\Finances\StudentCategory;
use Illuminate\Foundation\Http\FormRequest;

class RegistrationMhsRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $isUpdate = $this->method() !== 'POST';

    return [
      'nim' => [
        $isUpdate ? 'sometimes' : 'required',
        'exists:students,nim'
      ],
      'registration_batch_uuid' => [
        $isUpdate ? 'sometimes' : 'required',
        'exists:registration_batches,uuid'
      ],
      'shipping_address' => [
        $isUpdate ? 'sometimes' : 'required',
        'string'
      ],
      'student_category' => [
        $isUpdate ? 'sometimes' : 'required',
        'string',
        StudentCategory::toValidation()
      ],
      'payment_system' => [
        $isUpdate ? 'sometimes' : 'required',
        'string',
        PaymentSystem::toValidation()
      ],
      'program_type' => [
        $isUpdate ? 'sometimes' : 'required',
        'string',
        ProgramType::toValidation()
      ],
      'tutorial_service' => [
        $isUpdate ? 'sometimes' : 'required',
        'boolean'
      ],
      'semester' => [
        $isUpdate ? 'sometimes' : 'required',
        'string'
      ],
      'interested_spp' => [
        $isUpdate ? 'sometimes' : 'required',
        'boolean'
      ],
      'is_update' => 'sometimes|boolean',
    ];
  }

  public function messages(): array
  {
    return [
      '*.required' => ':attribute harus tidak boleh dikosongkan',
      '*.exists' => ':attribute tidak ditemukan di database',
      '*.string' => ':attribute harus berupa string',
      '*.boolean' => ':attribute harus berupa boolean (true/false)',
    ];
  }

  public function attributes(): array
  {
    return [
      'nim' => 'NIM Mahasiswa',
      'registration_batch_uuid' => 'Batch Pendaftaran',
      'shipping_address' => 'Alamat Pengiriman',
      'student_category' => 'Kategori Mahasiswa',
      'payment_system' => 'Sistem Pembayaran',
      'program_type' => 'Jenis Program',
      'tutorial_service' => 'Layanan Tutorial',
      'semester' => 'Semester',
      'interested_spp' => 'Tertarik SPP',
      'is_update' => 'Status Update'
    ];
  }
}
