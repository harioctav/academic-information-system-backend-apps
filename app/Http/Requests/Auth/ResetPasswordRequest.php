<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
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
      'token' => 'required',
      'email' => 'required|email',
      'password' => [
        'required',
        'string',
        'min:8',
        'confirmed',
        Password::defaults(),
        'regex:/^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{8,}$/'
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
      '*.email' => ':attribute tidak valid, harus berupa email',
      '*.exists' => ':attribute tidak ditemukan atau tidak valid',
      '*.min' => ':attribute terlalu pendek, minimal :min karakter',
      '*.confirmed' => ':attribute tidak sama dengan Kata Sandi Konfimasi',
      '*.regex' => ':attribute harus memiliki minimal 8 karakter dan mengandung kombinasi angka dan karakter spesial'
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
      'password' => 'Kata Sandi Saat Ini',
      'password_confirmatin' => 'Kata Sandi Konfirmasi',
    ];
  }
}
