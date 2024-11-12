<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Helpers\SeederProgressBar;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // reset cahced roles and permission
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $roles = UserRole::toArray();

    $this->command->warn(PHP_EOL . 'Creating roles...');
    $items = SeederProgressBar::withProgressBar(
      $this->command,
      count($roles),
      function () use ($roles) {
        static $index = 0;

        return collect([[
          'uuid' => Str::uuid(),
          'name' => $roles[$index++],
          'guard_name' => 'api',
          'created_at' => now(),
          'updated_at' => now(),
        ]]);
      }
    );

    Role::insert($items->toArray());
    $this->command->info('Roles created successfully!');
  }
}
