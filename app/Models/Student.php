<?php

namespace App\Models;

use App\Enums\Academics\AddressType;
use App\Enums\Academics\StudentRegistrationStatus;
use App\Enums\GenderType;
use App\Enums\GeneralConstant;
use App\Enums\ReligionType;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
  use HasUuid;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'uuid',
    'major_id',
    'nim',
    'nik',
    'name',
    'email',
    'birth_date',
    'birth_place',
    'gender',
    'phone',
    'religion',
    'initial_registration_period',
    'origin_department',
    'upbjj',
    'status_activity',
    'status_registration',
    'student_photo_path',
    'parent_name',
    'parent_phone_number',
  ];

  /**
   * Get the route key for the model.
   */
  public function getRouteKeyName(): string
  {
    return 'uuid';
  }

  /**
   * The relationships that should always be loaded.
   *
   * @var array<string>
   */
  protected $with = [
    'major'
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected $casts = [
    'status_registration' => StudentRegistrationStatus::class,
    'status_activity' => GeneralConstant::class,
    'gender' => GenderType::class,
    'religion' => ReligionType::class
  ];

  /**
   * Get the major that owns the major.
   *
   * @return BelongsTo
   */
  public function major(): BelongsTo
  {
    return $this->belongsTo(Major::class);
  }

  /**
   * Get the addresses associated with the student.
   *
   * @return HasMany
   */
  public function addresses(): HasMany
  {
    return $this->hasMany(StudentAddress::class);
  }

  /**
   * Get the student's domicile address.
   *
   * @return HasOne
   */
  public function domicileAddress(): HasOne
  {
    return $this->hasOne(StudentAddress::class)
      ->where('type', AddressType::Domicile->value);
  }

  /**
   * Get the student's ID card address.
   *
   * @return HasOne
   */
  public function idCardAddress(): HasOne
  {
    return $this->hasOne(StudentAddress::class)
      ->where('type', AddressType::IdCard->value);
  }

  /**
   * Get the recommendations associated with the student.
   *
   * @return HasMany
   */
  public function recommendations(): HasMany
  {
    return $this->hasMany(Recommendation::class);
  }

  /**
   * Get the grades associated with the student.
   *
   * @return HasMany
   */
  public function grades(): HasMany
  {
    return $this->hasMany(Grade::class);
  }

  /**
   * Get the URL of the student's photo.
   *
   * If the student has a photo uploaded, this will return the URL to the photo.
   * Otherwise, it will return null.
   *
   * @return string|null The URL of the student's photo, or null if no photo is available.
   */
  public function getStudentPhotoUrlAttribute()
  {
    return $this->student_photo_path
      ? Storage::url($this->student_photo_path)
      : null;
  }

  /**
   * Get the province that the student's village belongs to.
   *
   */
  public function getProvinceAttribute()
  {
    return optional(optional(optional(optional($this->domicileAddress->village)->district)->regency)->province);
  }

  /**
   * Get the regency that the student's village belongs to.
   *
   */
  public function getRegencyAttribute()
  {
    return optional(optional(optional($this->domicileAddress->village)->district)->regency);
  }

  /**
   * Get the district that the student's village belongs to.
   *
   */
  public function getDistrictAttribute()
  {
    return optional(optional($this->domicileAddress->village)->district);
  }

  /**
   * Get the village that the student's domicile address belongs to.
   *
   */
  public function getVillageAttribute()
  {
    return optional($this->domicileAddress)->village;
  }
}
