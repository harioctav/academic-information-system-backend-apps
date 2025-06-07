<?php

namespace Database\Seeders;

use App\Helpers\SeederProgressBar;
use App\Models\Registration;
use App\Models\RegistrationBatch;
use App\Models\Student;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RegistrationSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating Registrations data...');

    $faker = Factory::create('id_ID');

    $batchIds = RegistrationBatch::pluck('id')->toArray();
    $students = Student::with(['domicileAddress.village.district.regency.province'])->get();

    if (empty($batchIds)) {
      $this->command->error('No registration batches found. Please run RegistrationBatchSeeder first.');
      return;
    }

    if ($students->isEmpty()) {
      $this->command->error('No students found. Please run StudentSeeder first.');
      return;
    }

    DB::beginTransaction();

    try {
      SeederProgressBar::withProgressBar($this->command, min(150, $students->count()), function () use ($batchIds, $students, $faker) {
        $student = $students->random();

        // Build shipping address from student's domicile address
        $address = '';
        if ($student->domicileAddress) {
          $addressParts = [];

          if ($student->domicileAddress->village) {
            $addressParts[] = $student->domicileAddress->village->name;
          }
          if ($student->domicileAddress->village && $student->domicileAddress->village->district) {
            $addressParts[] = $student->domicileAddress->village->district->name;
          }
          if ($student->domicileAddress->village && $student->domicileAddress->village->district && $student->domicileAddress->village->district->regency) {
            $addressParts[] = $student->domicileAddress->village->district->regency->name;
          }
          if ($student->domicileAddress->village && $student->domicileAddress->village->district && $student->domicileAddress->village->district->regency && $student->domicileAddress->village->district->regency->province) {
            $addressParts[] = $student->domicileAddress->village->district->regency->province->name;
          }

          if ($student->domicileAddress->address) {
            $addressParts[] = $student->domicileAddress->address;
          }

          $address = implode(', ', array_filter($addressParts));
        }

        // Generate registration number
        $year = $faker->numberBetween(2020, 2024);
        $sequence = $faker->unique()->numberBetween(1, 9999);
        $registrationNumber = 'REG-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        // Random registration data
        $studentCategories = ['maba', 'mahasiswa_lama', 'pindahan', 'alih_kredit'];
        $paymentSystems = ['sipas', 'bank_transfer', 'virtual_account', 'e_wallet'];
        $programTypes = ['spp', 'non_spp', 'beasiswa', 'cicilan'];

        $registration = Registration::create([
          'uuid' => Str::uuid(),
          'registration_batch_id' => $faker->randomElement($batchIds),
          'registration_number' => $registrationNumber,
          'student_id' => $student->id,
          'shipping_address' => $address ?: $faker->address(),
          'student_category' => $faker->randomElement($studentCategories),
          'payment_system' => $faker->randomElement($paymentSystems),
          'program_type' => $faker->randomElement($programTypes),
          'tutorial_service' => $faker->boolean(70), // 70% chance true
          'semester' => $faker->numberBetween(1, 8),
          'interested_spp' => $faker->boolean(80), // 80% chance true
          'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
          'updated_at' => now(),
        ]);

        return collect([$registration]);
      });

      DB::commit();

      $this->command->info('Registrations created successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      $this->command->error('Error creating registrations: ' . $e->getMessage());
      throw $e;
    }
  }
}
