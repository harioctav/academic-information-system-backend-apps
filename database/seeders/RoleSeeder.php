<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Helpers\SeederProgressBar;
use App\Models\Permission;
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
    $roleRecords = [];

    $this->command->warn(PHP_EOL . 'Creating roles...');

    foreach ($roles as $role) {
      $roleRecords[] = [
        'uuid' => Str::uuid(),
        'name' => $role,
        'guard_name' => 'api',
        'created_at' => now(),
        'updated_at' => now(),
      ];
    }

    Role::insert($roleRecords);

    // Assign permissions to specific roles
    $adminRole = Role::where('name', UserRole::SuperAdmin->value)->first();
    $adminRole->syncPermissions(Permission::all());

    $regisRole = Role::where('name', UserRole::SubjectRegisTeam->value)->first();
    $regisRole->syncPermissions([
      'provinces.index',
      'provinces.show',
    ]);

    $this->command->info('Roles created successfully!');
  }
}
