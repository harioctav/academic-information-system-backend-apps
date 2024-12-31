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
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating Students data...');

    $faker = Factory::create('id_ID');

    $majorIds = Major::pluck('id')->toArray();
    $villageIds = Village::pluck('id')->toArray();

    DB::beginTransaction();

    SeederProgressBar::withProgressBar($this->command, 350, function () use ($majorIds, $villageIds, $faker) {
      $registrationYear = $faker->numberBetween(2020, 2024);
      $registrationPeriod = $faker->randomElement(['GANJIL', 'GENAP']);
      $initialRegistrationPeriod = $registrationYear . ' ' . $registrationPeriod;

      $major = Major::find($faker->randomElement($majorIds));
      $nim = $major->code . $registrationYear . str_pad($faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT);

      $student = Student::create([
        'uuid' => Str::uuid(),
        'major_id' => $major->id,
        'nim' => $nim,
        'nik' => $faker->unique()->nik(),
        'name' => $faker->name(),
        'email' => $faker->unique()->safeEmail(),
        'birth_date' => $faker->date(),
        'birth_place' => $faker->city(),
        'gender' => $faker->randomElement(
          array_filter(
            GenderType::cases(),
            fn($gender) => $gender !== GenderType::Unknown
          )
        ),
        'phone' => '628' . $faker->unique()->numerify('##########'),
        'religion' => $faker->randomElement(
          array_filter(
            ReligionType::cases(),
            fn($religion) => $religion !== ReligionType::Unknown
          )
        ),
        'initial_registration_period' => $initialRegistrationPeriod,
        'origin_department' => $faker->randomElement(['SMA', 'SMK', 'MA']),
        'upbjj' => $faker->city(),
        'status_registration' => $faker->randomElement(
          array_filter(
            StudentRegistrationStatus::cases(),
            fn($status) => $status !== StudentRegistrationStatus::Unknown
          )
        ),
        'status_activity' => $faker->boolean(80),
        'parent_name' => $faker->name(),
        'parent_phone_number' => '628' . $faker->unique()->numerify('##########'),
      ]);

      $addresses = [
        [
          'uuid' => Str::uuid(),
          'student_id' => $student->id,
          'village_id' => $faker->randomElement($villageIds),
          'type' => AddressType::Domicile->value,
          'address' => $faker->streetAddress(),
          'created_at' => now(),
          'updated_at' => now(),
        ],
        [
          'uuid' => Str::uuid(),
          'student_id' => $student->id,
          'village_id' => $faker->randomElement($villageIds),
          'type' => AddressType::IdCard->value,
          'address' => $faker->streetAddress(),
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
