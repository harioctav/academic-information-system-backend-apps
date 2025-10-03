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
      'registration_id' => 'required|exists:registrations,id',
      'registration_period' => 'nullable|string',
      'billing_code' => 'nullable|string',
      'billing_status' => 'nullable|string',
      'bank_fee' => 'required|numeric',
      'salut_member_fee' => 'required|numeric',
      'semester_fee' => 'required|numeric',
      'total_fee' => 'required|numeric',
      'settlement_status' => 'required|string',
      'settlement_date' => 'nullable|date',
      'note' => 'nullable|string',
    ];
  }

  public function authorize(): bool
  {
    return true;
  }

  public function messages(): array
  {
    return [
      '*.required' => ':attribute harus tidak boleh dikosongkan',
      '*.exists' => ':attribute tidak ditemukan di database',
      '*.string' => ':attribute harus berupa string',
      '*.numeric' => ':attribute harus berupa angka',
      '*.date' => ':attribute harus berupa tanggal yang valid',
    ];
  }

  public function attributes(): array
  {
    return [
      'student_id' => 'Mahasiswa',
      'registration_id' => 'Pendaftaran',
      'registration_period' => 'Periode Pendaftaran',
      'billing_code' => 'Kode Tagihan',
      'billing_status' => 'Status Tagihan',
      'bank_fee' => 'Biaya Bank',
      'salut_member_fee' => 'Biaya Anggota Salut',
      'semester_fee' => 'Biaya Semester',
      'total_fee' => 'Total Biaya',
      'settlement_status' => 'Status Penyelesaian',
      'settlement_date' => 'Tanggal Penyelesaian',
      'note' => 'Catatan'
    ];
  }
}
