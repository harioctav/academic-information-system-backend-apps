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

    // Permissions khusus untuk SubjectRegisTeam
    $regisTeamPermissions = [
      // Majors
      'majors.index',
      'majors.show',

      // Major Subjects
      'majors.subjects.index',
      'majors.subjects.store',
      'majors.subjects.update',
      'majors.subjects.destroy',
      'majors.subjects.bulk',

      // Subjects
      'subjects.index',
      'subjects.store',
      'subjects.show',
      'subjects.update',
      'subjects.destroy',
      'subjects.bulk',

      // Students
      'students.index',
      'students.store',
      'students.show',
      'students.update',
      'students.destroy',
      'students.bulk',
      'students.import',

      // Grades
      'grades.index',
      'grades.store',
      'grades.show',
      'grades.update',
      'grades.destroy',
      'grades.bulk',
      'grades.export',
      'grades.import',

      // Recommendations
      'recommendations.index',
      'recommendations.store',
      'recommendations.show',
      'recommendations.update',
      'recommendations.destroy',
      'recommendations.bulk',
    ];

    // Berikan semua permission ke SuperAdmin
    $adminRole = Role::where('name', UserRole::SuperAdmin->value)->first();
    $adminRole->syncPermissions(Permission::all());

    // Berikan permissions ke SubjectRegisTeam
    $regisTeamRole = Role::where('name', UserRole::SubjectRegisTeam->value)->first();
    $regisTeamRole->syncPermissions(array_merge($defaultPermissions, $regisTeamPermissions));

    // Berikan default permissions ke role lainnya
    $otherRoles = Role::whereNotIn('name', [UserRole::SuperAdmin->value, UserRole::SubjectRegisTeam->value])->get();
    foreach ($otherRoles as $role) {
      $role->syncPermissions($defaultPermissions);
    }

    $this->command->info('Roles created successfully!');
  }
}
