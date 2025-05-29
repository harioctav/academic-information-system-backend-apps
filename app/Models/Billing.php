<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use HasUuid;
    use HasFactory;

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

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
