<?php

namespace Database\Seeders;

use App\Helpers\SeederProgressBar;
use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
  /**
   * An associative array that defines the permissions used in the application.
   * Each key in the array represents a permission name, and the corresponding value
   * is the permission category ID that the permission belongs to.
   * This array is used to associate each permission with a specific category when
   * creating the permissions in the database.
   */
  protected array $permissions = [
    // API Users
    'users.index',
    'users.store',
    'users.show',
    'users.update',
    'users.destroy',

    // API Roles
    'roles.index',
    'roles.store',
    'roles.show',
    'roles.update',
    'roles.destroy',

    // API Provinces
    'provinces.index',
    'provinces.store',
    'provinces.show',
    'provinces.update',
    'provinces.destroy',

    // API Regencies
    'regencies.index',
    'regencies.store',
    'regencies.show',
    'regencies.update',
    'regencies.destroy',

    // API Districts
    'districts.index',
    'districts.store',
    'districts.show',
    'districts.update',
    'districts.destroy',

    // API Villages
    'villages.index',
    'villages.store',
    'villages.show',
    'villages.update',
    'villages.destroy',

    // API Majors
    'majors.index',
    'majors.store',
    'majors.show',
    'majors.update',
    'majors.destroy',

    // API Subjects
    'subjects.index',
    'subjects.store',
    'subjects.show',
    'subjects.update',
    'subjects.destroy',

    // API Students
    'students.index',
    'students.store',
    'students.show',
    'students.update',
    'students.destroy',
  ];

  /**
   * An associative array that maps permission names to their corresponding permission category IDs.
   * This is used to associate each permission with a specific category when creating the permissions.
   */
  protected array $permissionCategoryId = [
    'users' => 1,
    'roles' => 2,
    'provinces' => 3,
    'regencies' => 4,
    'districts' => 5,
    'villages' => 6,
    'majors' => 7,
    'subjects' => 8,
    'students' => 9,
  ];

  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $this->command->warn(PHP_EOL . 'Creating permissions...');
    $items = SeederProgressBar::withProgressBar(
      $this->command,
      count($this->permissions),
      function () {
        static $index = 0;
        $permission = $this->permissions[$index++];
        $category = explode('.', $permission)[0];

        return collect([[
          'uuid' => Str::uuid(),
          'name' => $permission,
          'permission_category_id' => $this->permissionCategoryId[$category],
          'guard_name' => 'api',
          'created_at' => now(),
          'updated_at' => now(),
        ]]);
      }
    );

    Permission::insert($items->toArray());
    $this->command->info('Permissions created successfully!');
  }
}
