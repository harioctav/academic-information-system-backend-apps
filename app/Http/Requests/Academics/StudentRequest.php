<?php

namespace App\Http\Requests\Academics;

use App\Enums\Academics\AddressType;
use App\Enums\Academics\StudentRegistrationStatus;
use App\Enums\GenderType;
use App\Enums\GeneralConstant;
use App\Enums\ReligionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class StudentRequest extends FormRequest
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
      // Basic Information
      'nim' => [
        'required',
        'numeric',
        Rule::unique('students', 'nim')->ignore($this->student),
      ],
      'nik' => [
        'nullable',
        'numeric',
        'digits:16',
        Rule::unique('students', 'nik')->ignore($this->student),
      ],
      'name' => 'required|string|max:100',
      'email' => [
        'nullable',
        'email:dns',
        'max:100',
        Rule::unique('students', 'email')->ignore($this->student),
      ],
      'birth_place' => 'nullable|string|max:50',
      'birth_date' => 'nullable|date',
      'gender' => [
        'required',
        'string',
        GenderType::toValidation()
      ],
      'religion' => [
        'required',
        'string',
        ReligionType::toValidation()
      ],
      'status_registration' => [
        'nullable',
        'string',
        StudentRegistrationStatus::toValidation()
      ],
      'status_activity' => [
        'nullable',
        'string',
        GeneralConstant::toValidation()
      ],
      'phone' => [
        'required',
        'numeric',
        Rule::unique('students', 'phone')->ignore($this->student),
      ],

      // Academic Information
      'major' => 'required|exists:majors,id',
      'initial_registration_period' => [
        'nullable',
        'string',
        'regex:/^[0-9]{4}\s(GANJIL|GENAP)$/',
        function ($attribute, $value, $fail) {
          if (!preg_match('/^[0-9]{4}\s(GANJIL|GENAP)$/', $value)) {
            return $fail("$attribute harus dalam format 'YYYY GANJIL' atau 'YYYY GENAP'.");
          }

          $parts = explode(' ', $value);
          $year = (int)$parts[0];
          $semester = $parts[1];
          $currentYear = date('Y');

          if ($year < 2019 || $year > ($currentYear + 1)) {
            return $fail("$attribute harus berada di antara 2019 dan " . ($currentYear + 1) . ".");
          }

          if (!in_array($semester, ['GANJIL', 'GENAP'])) {
            return $fail("$attribute harus berupa 'GANJIL' atau 'GENAP'.");
          }
        },
      ],
      'origin_department' => 'nullable|string',
      'upbjj' => 'nullable|string',

      // Address Rules
      'addresses' => 'required|array',
      'addresses.*.type' => [
        'required',
        'string',
        AddressType::toValidation()
      ],
      'addresses.*.province_id' => 'required|exists:provinces,id',
      'addresses.*.regency_id' => 'required|exists:regencies,id',
      'addresses.*.district_id' => 'required|exists:districts,id',
      'addresses.*.village_id' => 'required|exists:villages,id',
      'addresses.*.postal_code' => 'required|string',
      'addresses.*.address' => 'required|string',

      // Additional Information
      'student_photo_path' => 'nullable|mimes:jpg,png,jpeg|max:3048',
      'parent_name' => 'nullable|string|max:100',
      'parent_phone_number' => [
        'nullable',
        'numeric',
        Rule::unique('students', 'parent_phone_number')->ignore($this->student),
      ],
    ];
  }

  public function prepareForValidation()
  {
    if ($this->has('initial_registration_period')) {
      $this->merge([
        'initial_registration_period' => strtoupper($this->initial_registration_period)
      ]);
    }
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
      '*.min' => ':attribute maksimal :min karakter',
      '*.in' => ':attribute harus salah satu dari jenis berikut: :values',
      '*.unique' => ':attribute sudah digunakan, silahkan pilih yang lain',
      '*.exists' => ':attribute tidak ditemukan atau tidak bisa diubah',
      '*.regex' => ":attribute harus dalam format 'YYYY GANJIL' atau 'YYYY GENAP'.",
      '*.numeric' => ':attribute input tidak valid atau harus berupa angka',
      '*.image' => ':attribute tidak valid, pastikan memilih gambar',
      '*.mimes' => ':attribute tidak valid, masukkan gambar dengan format jpg atau png',
      '*.max' => ':attribute terlalu besar, maksimal :max karakter',
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
      'nim' => 'Nomor Induk Mahasiswa',
      'nik' => 'Nomor Induk Kependudukan',
      'name' => 'Nama Lengkap',
      'email' => 'Email',
      'birth_place' => 'Tempat Lahir',
      'birth_date' => 'Tanggal Lahir',
      'gender' => 'Jenis Kelamin',
      'religion' => 'Agama',
      'status_registration' => 'Status Registrasi',
      'status_activity' => 'Status Keaktifan',
      'phone' => 'No. Whatsapp',
      'major' => 'Program Studi',
      'province' => 'Provinsi',
      'regency' => 'Kabupaten',
      'district' => 'Kecamatan',
      'village' => 'Kelurahan',
      'post_code' => 'Kode Pos',
      'addresses' => 'Alamat Lengkap',
      'student_photo_path' => 'Foto',
      'initial_registration_period' => 'Tahun Masuk',
      'origin_department' => 'Jurusan Asal',
      'upbjj' => 'UPBJJ',
      'parent_name' => 'Nama Orang Tua',
      'parent_phone_number' => 'No. Telepon Orang Tua',
    ];
  }
}
