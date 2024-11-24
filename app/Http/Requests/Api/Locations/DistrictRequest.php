<?php

namespace App\Http\Requests\Api\Locations;

use Illuminate\Foundation\Http\FormRequest;

class DistrictRequest extends FormRequest
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
      'regencies' => [
        'required',
        'exists:regencies,id'
      ],
      'code' => [
        'required',
        'numeric',
      ],
      'name' => [
        'required',
        'string',
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
      'regencies' => 'Kota atau Kabupaten',
      'code' => 'Kode Kecamatan',
      'name' => 'Nama Kecamatan',
    ];
  }
}
