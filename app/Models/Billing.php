<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Billing extends Model
{
  use HasFactory, HasUuid;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'uuid',
    'student_id',
    'registration_id',
    'registration_period',
    'billing_code',
    'billing_status',
    'bank_fee',
    'salut_member_fee',
    'semester_fee',
    'total_fee',
    'settlement_status',
    'settlement_date',
    'note',
  ];

  /**
   * The attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected $casts = [
    'bank_fee' => 'integer',
    'salut_member_fee' => 'integer',
    'semester_fee' => 'integer',
    'total_fee' => 'integer',
    'settlement_date' => 'date',
  ];

  /**
   * Get the route key for the model.
   */
  public function getRouteKeyName(): string
  {
    return 'uuid';
  }

  /**
   * Get the student that owns the billing.
   */
  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class);
  }

  /**
   * Get the registration that owns the billing.
   */
  public function registration(): BelongsTo
  {
    return $this->belongsTo(Registration::class);
  }
}
