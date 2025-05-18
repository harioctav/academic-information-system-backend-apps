<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'student_id',
        'registration_id',
        'billing_id',
        'payment_type',
        'payment_method',
        'payment_status',
        'payment_date',
        'amount_paid',
        'proof_of_payment',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function billing()
    {
        return $this->belongsTo(Billing::class);
    }
}
