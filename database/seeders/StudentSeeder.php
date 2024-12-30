<?php

namespace Database\Seeders;

use App\Enums\Academics\AddressType;
use App\Enums\Academics\StudentRegistrationStatus;
use App\Enums\GenderType;
use App\Enums\ReligionType;
use App\Helpers\SeederProgressBar;
use App\Models\Major;
use App\Models\Student;
use App\Models\StudentAddress;
use App\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating Students data...');

    $majorIds = Major::pluck('id')->toArray();
    $villageIds = Village::pluck('id')->toArray();

    DB::beginTransaction();

    SeederProgressBar::withProgressBar($this->command, 350, function () use ($majorIds, $villageIds) {
      $registrationYear = fake()->numberBetween(2020, 2024);
      $registrationPeriod = fake()->randomElement(['GANJIL', 'GENAP']);
      $initialRegistrationPeriod = $registrationYear . ' ' . $registrationPeriod;

      $major = Major::find(fake()->randomElement($majorIds));
      $nim = $major->code . $registrationYear . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT);

      $student = Student::create([
        'uuid' => Str::uuid(),
        'major_id' => $major->id,
        'nim' => $nim,
        'nik' => fake()->unique()->numerify('################'),
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'birth_date' => fake()->date(),
        'birth_place' => fake()->city(),
        'gender' => fake()->randomElement(
          array_filter(
            GenderType::cases(),
            fn($gender) => $gender !== GenderType::Unknown
          )
        ),
        'phone' => fake()->unique()->e164PhoneNumber(),
        'religion' => fake()->randomElement(
          array_filter(
            ReligionType::cases(),
            fn($religion) => $religion !== ReligionType::Unknown
          )
        ),
        'initial_registration_period' => $initialRegistrationPeriod,
        'origin_department' => fake()->randomElement(['SMA', 'SMK', 'MA']),
        'upbjj' => fake()->city(),
        'status_registration' => fake()->randomElement(
          array_filter(
            StudentRegistrationStatus::cases(),
            fn($status) => $status !== StudentRegistrationStatus::Unknown
          )
        ),
        'status_activity' => fake()->boolean(80),
        'parent_name' => fake()->name(),
        'parent_phone_number' => fake()->unique()->e164PhoneNumber(),
      ]);

      $addresses = [
        [
          'uuid' => Str::uuid(),
          'student_id' => $student->id,
          'village_id' => fake()->randomElement($villageIds),
          'type' => AddressType::Domicile->value,
          'address' => fake()->streetAddress(),
          'created_at' => now(),
          'updated_at' => now(),
        ],
        [
          'uuid' => Str::uuid(),
          'student_id' => $student->id,
          'village_id' => fake()->randomElement($villageIds),
          'type' => AddressType::IdCard->value,
          'address' => fake()->streetAddress(),
          'created_at' => now(),
          'updated_at' => now(),
        ]
      ];

      StudentAddress::insert($addresses);

      return collect([$student]);
    });

    DB::commit();

    $this->command->info('Students created successfully!');
  }
}
