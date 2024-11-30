<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\GeneralConstant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable, HasApiTokens, HasUuid, HasRoles;

  /**
   * The guard name used for authentication.
   *
   * @var string
   */
  protected string $guard_name = 'api';

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'uuid',
    'name',
    'email',
    'phone',
    'password',
    'photo_profile_path',
    'status',
    'last_activity',
    'failed_login_attempts',
    'locked_until'
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
      'status' => GeneralConstant::class,
      'last_activity' => 'datetime',
      'locked_until' => 'datetime'
    ];
  }

  /**
   * Validate the password of the user for the Passport password grant.
   */
  public function validateForPassportPasswordGrant(string $password): bool
  {
    return Hash::check($password, $this->password);
  }

  /**
   * Get the login activities associated with the user.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function loginActivities(): HasMany
  {
    return $this->hasMany(LoginActivity::class);
  }

  /**
   * Determines if the user's account is currently locked.
   *
   * @return bool True if the user's account is locked, false otherwise.
   */
  public function isLocked(): bool
  {
    return $this->locked_until && $this->locked_until->isFuture();
  }
}
