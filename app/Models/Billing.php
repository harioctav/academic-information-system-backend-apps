<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'student_id',
        'billing_code',
        'bank_fee',
        'salut_member_fee',
        'semester_fee',
        'total_fee',
        'payment_method',
        'payment_status',
        'note',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
