<?php

namespace Database\Seeders;

use App\Helpers\SeederProgressBar;
use App\Models\RegistrationBatch;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RegistrationBatchSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating Registration Batches data...');

    $faker = Factory::create('id_ID');

    DB::beginTransaction();

    try {
      SeederProgressBar::withProgressBar($this->command, 10, function () use ($faker) {
        // Generate random year and period
        $registrationYear = $faker->numberBetween(2020, 2024);
        $registrationPeriod = $faker->randomElement(['Ganjil', 'Genap']);

        // Generate batch name and description
        $batchName = "Registrasi Matakuliah {$registrationYear}";
        $periodCode = $registrationPeriod === 'Ganjil' ? '1' : '2';
        $description = "Registrasi Mata Kuliah Masa Reg.{$registrationYear}.{$periodCode} / {$registrationYear} {$registrationPeriod}";

        // Generate random dates
        $startDate = $faker->dateTimeBetween('-30 days', '+5 days');
        $endDate = $faker->dateTimeBetween($startDate, '+60 days');

        // Generate notes based on period and year
        $noteTemplates = [
          "Batch registrasi untuk mahasiswa {$registrationPeriod} {$registrationYear}",
          "Periode pendaftaran mata kuliah semester {$registrationPeriod}",
          "Batch {$registrationPeriod} tahun akademik {$registrationYear}",
          "Registrasi mahasiswa baru dan lama semester {$registrationPeriod}",
          "Batch pendaftaran KRS {$registrationYear} {$registrationPeriod}",
          "Periode registrasi mata kuliah {$registrationYear}",
          "Batch khusus mahasiswa semester {$registrationPeriod}"
        ];

        $registrationBatch = RegistrationBatch::create([
          'uuid' => Str::uuid(),
          'name' => $batchName,
          'description' => $description,
          'start_date' => $startDate,
          'end_date' => $endDate,
          'notes' => $faker->randomElement($noteTemplates),
          'created_at' => now(),
          'updated_at' => now(),
        ]);

        return collect([$registrationBatch]);
      });

      DB::commit();

      $this->command->info('Registration Batches created successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      $this->command->error('Error creating registration batches: ' . $e->getMessage());
      throw $e;
    }
  }
}
