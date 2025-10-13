<?php

namespace App\Models;

use App\Enums\GeneralConstant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class RegistrationBatch extends Model
{
  use HasFactory, HasUuid;

  protected $fillable = [
    'name',
    'description',
    'start_date',
    'end_date',
    'notes',
    'status',
  ];

  protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
    'status' => GeneralConstant::class,
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
