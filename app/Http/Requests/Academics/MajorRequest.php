<?php

namespace App\Http\Requests\Academics;

use App\Enums\Academics\DegreeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class MajorRequest extends FormRequest
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
      'code' => [
        'required',
        'string',
        'max:20',
        Rule::unique('majors', 'code')->ignore($this->major),
      ],
      'name' => [
        'required',
        'string',
        'max:100',
        Rule::unique('majors', 'name')->ignore($this->major),
      ],
      'degree' => [
        'required',
        DegreeType::toValidation()
      ]
    ];
  }

  /**
   * Make a capital letter at the end of each word.
   */
  public function validationData()
  {
    $data = $this->all();
    if (!empty($data['name'])) {
      $data['name'] = Str::title($data['name']);
    }
    return $data;
  }

  /**
   * Get the error messages for the defined validation rules.
   */
  public function messages(): array
  {
    return [
      '*.required' => ':attribute harus tidak boleh dikosongkan',
      '*.max' => ':attribute melebihi batas, maksimal :max',
      '*.in' => ':attribute harus salah satu dari data berikut: :values',
      '*.unique' => ':attribute sudah digunakan, silahkan pilih yang lain',
      '*.exists' => ':attribute tidak ditemukan atau tidak bisa diubah',
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
      'code' => 'Kode Jurusan',
      'name' => 'Nama Jurusan',
      'degree' => 'Jenjang atau Tingkatan',
      'total_course_credit' => 'Total sks ditempuh',
    ];
  }
}
