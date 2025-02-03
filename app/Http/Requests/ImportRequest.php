<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
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
      'file' => 'required|file|mimes:xls,xlsx|max:2048',
    ];
  }

  /**
   * Get the error messages for the defined validation rules.
   *
   */
  public function messages(): array
  {
    return [
      '*.required' => ':attribute Tidak boleh dikosongkan.',
      '*.file' => 'Unggahan harus berupa file.',
      '*.mimes' => 'File harus berupa file bertipe: xls, xlsx.',
      '*.max' => 'Ukuran file tidak boleh lebih dari 2MB.',
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
      'file' => 'File',
    ];
  }
}
