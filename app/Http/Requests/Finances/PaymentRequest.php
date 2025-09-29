<?php

namespace App\Http\Requests\Finances;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize()
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
      'billing_id' => 'nullable|exists:billings,id',
      'payment_method' => 'required|in:transfer,cash',
      'payment_plan' => 'required|in:cicil,lunas',
      'payment_date' => 'nullable|date',
      'amount_paid' => 'required|numeric|min:0',
      'transfer_to' => 'nullable|string',
      'proof_of_payment' => 'required|image',
      'payment_status' => 'required|in:pending,confirmed,rejected',
      'note' => 'nullable|string'
    ];
  }

  public function messages(): array
  {
    return [
      '*.required' => ':attribute harus tidak boleh dikosongkan',
      '*.exists' => ':attribute tidak ditemukan di database',
      '*.in' => ':attribute tidak valid',
      '*.numeric' => ':attribute harus berupa angka',
      '*.string' => ':attribute harus berupa string',
      '*.date' => ':attribute harus berupa tanggal yang valid',
      '*.min' => ':attribute minimal :min karakter atau lebih',
    ];
  }

  public function attributes(): array
  {
    return [
      'student_id' => 'Mahasiswa',
      'billing_id' => 'Tagihan',
      'payment_method' => 'Metode Pembayaran',
      'payment_plan' => 'Rencana Pembayaran',
      'payment_date' => 'Tanggal Pembayaran',
      'amount_paid' => 'Jumlah Dibayar',
      'transfer_to' => 'Transfer Ke',
      'proof_of_payment' => 'Bukti Pembayaran',
      'payment_status' => 'Status Pembayaran',
      'note' => 'Catatan'
    ];
  }
}
