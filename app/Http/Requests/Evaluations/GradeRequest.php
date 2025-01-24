<?php

namespace App\Http\Requests\Evaluations;

use App\Enums\Evaluations\GradeType;
use Illuminate\Foundation\Http\FormRequest;

class GradeRequest extends FormRequest
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
      'student_id' => 'required|exists:students,id',
      'subjects' => 'required|array|min:1|max:9',
      'subjects.*' => 'exists:subjects,id',
      'grade' => [
        'required',
        'string',
        'regex:/^[ABCDE][+-]?$/',
        function ($attribute, $value, $fail) {
          $validGrades = GradeType::toArray();
          if (!in_array($value, $validGrades)) {
            $fail('Nilai yang dimasukkan tidak valid.');
          }
        },
      ],
      'mutu' => 'nullable|string',
    ];
  }

  /**
   * Get the error messages for the defined validation rules.
   */
  public function messages(): array
  {
    return [
      '*.required' => ':attribute harus tidak boleh dikosongkan',
      '*.min' => ':attribute maksimal :min',
      '*.in' => ':attribute harus salah satu dari jenis berikut: :values',
      '*.unique' => ':attribute sudah digunakan, silahkan pilih yang lain',
      '*.exists' => ':attribute tidak ditemukan atau tidak bisa diubah',
      '*.numeric' => ':attribute input tidak valid atau harus berupa angka',
      '*.image' => ':attribute tidak valid, pastikan memilih gambar',
      '*.mimes' => ':attribute tidak valid, masukkan gambar dengan format jpg atau png',
      '*.regex' => ':attribute dimasukkan tidak valid',
      '*.max' => ':attribute terlalu besar, maksimal :max',
      '*.date' => ':attribute harus berupa tanggal',
      '*.digits' => ':attribute harus memiliki :digits angka',
      '*.between' => ':attribute harus berada diantara tahun :min sampai :max',
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
      'student_id' => 'Mahasiswa',
      'subject_id' => 'Matakuliah',
      'grade' => 'Nilai Mahasiswa',
      'mutu' => 'Nilai Ujian Mahasiswa',
    ];
  }
}
