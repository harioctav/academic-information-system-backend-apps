<?php

namespace App\Http\Requests\Locations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VillageRequest extends FormRequest
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
      'districts' => [
        'required',
        'exists:districts,id'
      ],
      'code' => [
        'required',
        'numeric',
      ],
      'name' => [
        'required',
        'string',
      ],
      'pos_code' => [
        'required',
        'numeric',
        Rule::unique('villages', 'pos_code')->ignore($this->village),
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
      'districts' => 'Kecamatan',
      'code' => 'Kode Desa atau Kelurahan',
      'name' => 'Nama Desa atau Kelurahan',
      'pos_code' => 'Kode Pos',
    ];
  }
}
