<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\GeneralConstant;
use App\Enums\UserRole;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class User extends Authenticatable
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable, HasApiTokens, HasUuid, HasRoles;

  /**
   * Sends a password reset notification to the user.
   *
   * @param string $token The password reset token.
   * @return void
   */
  public function sendPasswordResetNotification($token)
  {
    $this->notify(new ResetPasswordNotification($token));
  }

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

  /**
   * Get the role names associated with the user.
   *
   * This attribute returns a comma-separated string of the names of all the roles
   * associated with the user.
   *
   * @return string The comma-separated list of role names.
   */
  public function getRoleNameAttribute()
  {
    return $this->roles->implode('name');
  }

  /**
   * Get the role IDs associated with the user.
   *
   * This attribute returns a comma-separated string of the IDs of all the roles
   * associated with the user.
   *
   * @return string The comma-separated list of role IDs.
   */
  public function getRoleIdAttribute()
  {
    return $this->roles->implode('id');
  }

  /**
   * Scope a query to exclude users with the "Super Admin" role.
   *
   * @param  \Illuminate\Database\Eloquent\Builder  $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeWhereNotAdmin($query): Builder
  {
    return $query->whereDoesntHave('roles', function ($row) {
      $row->where('name', UserRole::SuperAdmin->value);
    });
  }

  /**
   * Scope a query to only include active users.
   * 
   */
  public function scopeActive($data)
  {
    return $data->where('status', true);
  }

  /**
   * Get the active users.
   *
   * This method returns a collection of all active users.
   *
   * @return \Illuminate\Database\Eloquent\Collection The collection of active users.
   */
  public function getActive(): Collection
  {
    return $this->active()->get();
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
