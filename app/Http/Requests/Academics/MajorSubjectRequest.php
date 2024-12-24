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
    return array_merge(
      $this->baseRules(),
      $this->methodSpecificRules()
    );
  }

  /**
   * Base validation rules that apply to all requests
   */
  private function baseRules(): array
  {
    return [
      'subjects' => 'required|array|min:1',
      'semester' => 'required|integer|between:1,8',
    ];
  }

  /**
   * Get method specific validation rules
   */
  private function methodSpecificRules(): array
  {
    return match ($this->method()) {
      'POST' => $this->createRules(),
      'PUT', 'PATCH' => $this->updateRules(),
      default => []
    };
  }

  /**
   * Validation rules specific to create operation
   */
  private function createRules(): array
  {
    return [
      'subjects.*' => [
        'exists:subjects,id',
        $this->validateNewSubject()
      ]
    ];
  }

  /**
   * Validation rules specific to update operation
   */
  private function updateRules(): array
  {
    return [
      'subjects.*' => [
        'exists:subjects,id',
        $this->validateExistingSubject()
      ]
    ];
  }

  /**
   * Custom validation for new subject assignment
   * Checks if subject is already assigned to the major
   */
  private function validateNewSubject(): \Closure
  {
    return function ($attribute, $value, $fail) {
      $major = $this->route('major');

      if (!$major) return;

      $existingSubject = \App\Models\MajorSubject::where('major_id', $major->id)
        ->where('subject_id', $value)
        ->first();

      if ($existingSubject) {
        $fail("Mata kuliah ini sudah ada di semester {$existingSubject->semester} untuk jurusan tersebut.");
      }
    };
  }

  /**
   * Custom validation for existing subject update
   * Allows updating if subject already exists
   */
  private function validateExistingSubject(): \Closure
  {
    return function ($attribute, $value, $fail) {
      $major = $this->route('major');
      $currentMajorSubject = $this->route('majorSubject');

      if ($major && $currentMajorSubject && $value != $currentMajorSubject->subject_id) {
        $existingSubject = \App\Models\MajorSubject::where('major_id', $major->id)
          ->where('subject_id', $value)
          ->first();

        if ($existingSubject) return;
      }
    };
  }

  /**
   * Get the error messages for the defined validation rules.
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
      '*.between' => ':attribute harus berada diantara :attribute :min sampai :max',
    ];
  }

  /**
   * Get custom attributes for validator errors.
   */
  public function attributes(): array
  {
    return [
      'subjects' => 'Matakuliah',
      'semester' => 'Semester',
    ];
  }
}
