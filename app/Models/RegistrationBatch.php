<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class RegistrationBatch extends Model
{
    use HasFactory;
    use HasUuid;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }
}
