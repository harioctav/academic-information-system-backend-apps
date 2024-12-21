<?php

namespace App\Http\Requests\Academics;

use Illuminate\Foundation\Http\FormRequest;

class MajorSubjectRequest extends FormRequest
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
      'subjects' => 'required|array|min:1',
      'subjects.*' => [
        'exists:subjects,id',
        function ($attribute, $value, $fail) {
          // Dapatkan major_id dari request atau context
          $major = $this->route('major'); // Asumsi ada 'major' di route

          if ($major) {
            // Cari subject berdasarkan major_id dan subject_id
            $existingSubject = \App\Models\MajorSubject::where('major_id', $major->id)
              ->where('subject_id', $value)
              ->first();

            // Jika subject sudah ada di jurusan tersebut, berikan pesan error
            if ($existingSubject) {
              $fail("Mata kuliah ini sudah ada di semester {$existingSubject->semester} untuk jurusan tersebut.");
            }
          }
        },
      ],
      'semester' => 'required|integer|between:1,8',
    ];
  }

  /**
   * Get the error messages for the defined validation rules.
   *
   */
  public function messages(): array
  {
    return [
      '*.required' => ':attribute tidak boleh dikosongkan',
      '*.string' => ':attribute tidak valid, masukkan yang benar',
      '*.max' => ':attribute terlalu panjang, maksimal :max.',
      '*.min' => ':attribute terlalu panjang, maksimal :min.',
      '*.integer' => ':attribute harus berupa angka',
      '*.in' => ':attribute tidak sesuai dengan data kami',
      '*.exists' => ':attribute tidak ditemukan di storage kami',
      '*.regex' => ':attribute harus dalam format angka.angka, misalnya 1.1 atau 2.5 dst',
      '*.between' => ':attribute harus berada diantara tahun :min sampai :max',
    ];
  }

  /**
   * Get custom attributes for validator errors.
   *
   */
  public function attributes(): array
  {
    return [
      'subjects' => 'Matakuliah',
      'semester' => 'Semester',
    ];
  }
}
