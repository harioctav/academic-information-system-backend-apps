<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'student_id',
        'address_id',
        'student_category',
        'payment_method',
        'program_type',
        'tutorial_service',
        'semester',
        'interested_spp',
    ];

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
}
