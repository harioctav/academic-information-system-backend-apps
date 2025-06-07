<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Registration;
use App\Models\Student;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $this->callSilent([
      PermissionCategorySeeder::class,
      PermissionSeeder::class,
      RoleSeeder::class,
      AdminSeeder::class,
      ProvinceSeeder::class,
      RegencySeeder::class,
      DistrictSeeder::class,
      VillageSeeder::class,
      MajorSubjectSeeder::class,
      StudentSeeder::class,
      RegistrationBatchSeeder::class,
      RegistrationSeeder::class,
      BillingSeeder::class,
    ]);
  }
}
