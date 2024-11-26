<?php

namespace Database\Seeders;

use App\Enums\UserRole;
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

    // Default permissions untuk semua role
    $defaultPermissions = [
      'users.profile',
      'users.password'
    ];

    // Berikan semua permission ke SuperAdmin
    $adminRole = Role::where('name', UserRole::SuperAdmin->value)->first();
    $adminRole->syncPermissions(Permission::all());

    // Berikan default permissions ke role lainnya
    $otherRoles = Role::where('name', '!=', UserRole::SuperAdmin->value)->get();
    foreach ($otherRoles as $role) {
      $role->syncPermissions($defaultPermissions);
    }

    $this->command->info('Roles created successfully!');
  }
}
