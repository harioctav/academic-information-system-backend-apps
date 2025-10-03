<?php

namespace App\Http\Requests\Finances;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationBatchRequest extends FormRequest
{
  public function rules(): array
  {
    return [
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'start_date' => 'required|date',
      'end_date' => 'required|date|after_or_equal:start_date',
      'notes' => 'nullable|string',
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
      '*.string' => ':attribute harus berupa string',
      '*.date' => ':attribute harus berupa tanggal yang valid',
      '*.after_or_equal' => ':attribute harus sama atau setelah tanggal mulai',
      '*.max' => ':attribute maksimal :max karakter',
    ];
  }

  public function attributes(): array
  {
    return [
      'name' => 'Nama Batch',
      'description' => 'Deskripsi',
      'start_date' => 'Tanggal Mulai',
      'end_date' => 'Tanggal Berakhir',
      'notes' => 'Catatan'
    ];
  }
}
