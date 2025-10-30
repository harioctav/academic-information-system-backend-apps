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
use Illuminate\Database\Eloquent\Model;

class StudentSeeder extends Seeder
{
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating Students data...');

    $faker = Factory::create('id_ID');

    // Preload majors with codes to avoid DB hit in the loop
    $majorIdToCode = Major::pluck('code', 'id')->toArray();
    $majorIds = array_keys($majorIdToCode);
    $villageIds = Village::pluck('id')->toArray();

    // Reduce memory during seeding
    DB::disableQueryLog();

    DB::beginTransaction();

    Model::withoutEvents(function () use ($majorIds, $majorIdToCode, $villageIds, $faker) {
      SeederProgressBar::withProgressBar($this->command, 350, function () use ($majorIds, $majorIdToCode, $villageIds, $faker) {
        $registrationYear = $faker->numberBetween(2020, 2024);
        $registrationPeriod = $faker->randomElement(['GANJIL', 'GENAP']);
        $initialRegistrationPeriod = "{$registrationYear} {$registrationPeriod}";

        $majorId = $faker->randomElement($majorIds);
        $majorCode = $majorIdToCode[$majorId] ?? 'XX';
        $randomNumber = str_pad($faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT);
        $nim = "{$majorCode}{$registrationYear}{$randomNumber}";

        $phonePrefix = "628";

        $student = Student::create([
          'uuid' => Str::uuid(),
          'major_id' => $majorId,
          'nim' => $nim,
          'nik' => $faker->unique()->nik(),
          'name' => "{$faker->firstName()} {$faker->lastName()}",
          'email' => $faker->unique()->safeEmail(),
          'birth_date' => $faker->date(),
          'birth_place' => $faker->city(),
          'gender' => $faker->randomElement(
            array_filter(
              GenderType::cases(),
              fn($gender) => $gender !== GenderType::Unknown
            )
          ),
          'phone' => "{$phonePrefix}{$faker->unique()->numerify('##########')}",
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
          'parent_name' => "{$faker->firstName()} {$faker->lastName()}",
          'parent_phone_number' => "{$phonePrefix}{$faker->unique()->numerify('##########')}",
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

        // Return empty collection to avoid memory accumulation inside helper
        return collect();
      });
    });

    DB::commit();

    $this->command->info('Students created successfully!');
  }
}
