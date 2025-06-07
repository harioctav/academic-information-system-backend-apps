<?php

namespace App\Http\Requests\Finances;

use Illuminate\Foundation\Http\FormRequest;

class BillingRequest extends FormRequest
{
  public function rules(): array
  {
    $billingId = $this->route('billing')?->id;

    return [
      'student_id' => 'required|exists:students,id',
      'registration_id' => 'required|exists:registrations,id',
      'registration_period' => 'nullable|string',
      'billing_code' => 'nullable|string',
      'billing_status' => 'nullable|string',
      'bank_fee' => 'required|numeric',
      'salut_member_fee' => 'required|numeric',
      'semester_fee' => 'required|numeric',
      'total_fee' => 'required|numeric',
      'settlement_status' => 'required|string',
      'settlement_date' => 'nullable|date',
      'note' => 'nullable|string',
    ];
  }

  public function authorize(): bool
  {
    return true;
  }
}
