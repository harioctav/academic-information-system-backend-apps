<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
      'email' => 'required|email',
      'password' => 'required',
      'remember' => 'boolean',
    ];
  }

  /**
   * Get the error messages for the defined validation rules.
   */
  public function messages(): array
  {
    return [
      '*.required' => ':attribute tidak boleh dikosongkan',
      '*.email' => ':attribute tidak valid, harus berupa email',
      '*.unique' => ':attribute sudah digunakan, silahkan pilih yang lain',
      '*.numeric' => ':attribute tidak valid, harus berupa angka',
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
      'email' => 'Email',
      'password' => 'Password',
    ];
  }
}
