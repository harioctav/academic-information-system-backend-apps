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
use Illuminate\Support\Facades\Storage;
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
   * Get the route key for the model.
   */
  public function getRouteKeyName(): string
  {
    return 'uuid';
  }

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
   * Gets the URL of the user's profile photo.
   *
   * If the `photo_profile_path` attribute is set, this method will return the URL of the
   * corresponding file in the storage. Otherwise, it will return `null`.
   *
   * @return string|null The URL of the user's profile photo, or `null` if no photo is set.
   */
  public function getPhotoUrlAttribute()
  {
    return $this->photo_profile_path
      ? Storage::url($this->photo_profile_path)
      : null;
  }

  public function getRoleNameAttribute()
  {
    return $this->roles->implode('name');
  }

  public function getRoleIdAttribute()
  {
    return $this->roles->implode('id');
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
