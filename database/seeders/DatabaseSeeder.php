<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
      UserSeeder::class,
      ProvinceSeeder::class,
      RegencySeeder::class,
      DistrictSeeder::class,
      VillageSeeder::class,
    ]);
  }
}
