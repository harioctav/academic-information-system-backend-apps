<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Registration;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // Reduce memory usage during large seed runs
    DB::disableQueryLog();

    $seeders = [
      PermissionCategorySeeder::class,
      PermissionSeeder::class,
      RoleSeeder::class,
      AdminSeeder::class,
      ProvinceSeeder::class,
      RegencySeeder::class,
      DistrictSeeder::class,
      VillageSeeder::class,
      MajorSubjectSeeder::class,
      // StudentSeeder::class,
      RegistrationBatchSeeder::class,
      // RegistrationSeeder::class,
      // BillingSeeder::class,
    ];

    Model::withoutEvents(function () use ($seeders) {
      foreach ($seeders as $seeder) {
        try {
          $this->call($seeder);
          if (isset($this->command)) {
            $this->command->info("Seeded: {$seeder}");
          }
        } catch (\Throwable $e) {
          Log::error('Seeder failed', [
            'seeder' => $seeder,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
          ]);
          if (isset($this->command)) {
            $this->command->warn("Seeder failed: {$seeder} → {$e->getMessage()}");
          }
          // Continue with the next seeder
        }
      }
    });
  }
}
