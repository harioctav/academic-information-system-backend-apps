<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Registration extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'uuid',
        'registration_number',
        'registration_batch_id',
        'student_id',
        'shipping_address',
        'student_category',
        'payment_system',
        'program_type',
        'tutorial_service',
        'semester',
        'interested_spp',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // Relasi ke student
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Relasi ke alamat student
    public function address()
    {
        return $this->belongsTo(StudentAddress::class, 'address_id');
    }

    // Relasi ke batch registrasi
    public function registrationBatch()
    {
        return $this->belongsTo(RegistrationBatch::class);
    }
}
