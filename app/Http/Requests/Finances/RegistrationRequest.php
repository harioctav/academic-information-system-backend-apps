<?php

namespace App\Http\Requests\Finances;

use App\Enums\Finances\PaymentSystem;
use App\Enums\Finances\ProgramType;
use App\Enums\Finances\StudentCategory;
use Illuminate\Foundation\Http\FormRequest;

class RegistrationRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
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
    $isUpdate = $this->method() !== 'POST';

    return [
      'registration_batch_id' => [
        $isUpdate ? 'sometimes' : 'required',
        'exists:registration_batches,id'
      ],
      'student_id' => [
        $isUpdate ? 'sometimes' : 'required',
        'exists:students,id'
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
    ];
  }

  /**
   * Get the error messages for the defined validation rules.
   */
  public function messages(): array
  {
    return [
      '*.required' => ':attribute harus tidak boleh dikosongkan',
      '*.exists' => ':attribute tidak ditemukan di database',
      '*.string' => ':attribute harus berupa string',
      '*.boolean' => ':attribute harus berupa boolean (true/false)',
    ];
  }

  /**
   * Get the validation attribute names that apply to the request.
   *
   * @return array<string, string>
   */
  public function attributes(): array
  {
    return [
      'registration_batch_id' => 'Batch Pendaftaran',
      'registration_batch_uuid' => 'Batch Pendaftaran',
      'student_id' => 'Mahasiswa',
      'nim' => 'NIM Mahasiswa',
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
