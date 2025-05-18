<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    protected $fillable = [
        'uuid',
        'student_id',
        'payment_method',
        'bank_fee',
        'subscription_fee',
        'subscription_code',
        'total_fee',
        'billing_code',
        'payment_status',
    ];

    protected static function booted()
    {
        static::creating(function ($invoice) {
            $invoice->uuid = Str::uuid();
        });
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
