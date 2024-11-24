<?php

namespace App\Http\Requests\Api\Locations;

use App\Enums\Locations\RegencyType;
use Illuminate\Foundation\Http\FormRequest;

class RegencyRequest extends FormRequest
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
    return [
      'provinces' => [
        'required',
        'exists:provinces,id'
      ],
      'code' => [
        'required',
        'numeric',
      ],
      'name' => [
        'required',
        'string',
      ],
      'type' => [
        'required',
        'string',
        RegencyType::toValidation()
      ],
    ];
  }

  /**
   * Get the error messages for the defined validation rules.
   */
  public function messages(): array
  {
    return [
      '*.required' => ':attribute tidak boleh dikosongkan',
      '*.unique' => ':attribute sudah digunakan, silahkan pilih yang lain',
      '*.numeric' => ':attribute tidak valid, harus berupa angka',
      '*.exists' => ':attribute tidak ditemukan atau tidak valid',
      '*.in' => ':attribute tidak sesuai dengan data kami',
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
      'provinces' => 'Provinsi',
      'code' => 'Kode Kota atau Kabupaten',
      'name' => 'Nama Kota atau Kabupaten',
      'type' => 'Tipe Kota atau Kabupaten',
    ];
  }
}
