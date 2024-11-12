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
    'api.users.index',
    'api.users.store',
    'api.users.show',
    'api.users.update',
    'api.users.destroy',

    // API Roles
    'api.roles.index',
    'api.roles.store',
    'api.roles.show',
    'api.roles.update',
    'api.roles.destroy',

    // API Provinces
    'api.provinces.index',
    'api.provinces.store',
    'api.provinces.show',
    'api.provinces.update',
    'api.provinces.destroy',

    // API Regencies
    'api.regencies.index',
    'api.regencies.store',
    'api.regencies.show',
    'api.regencies.update',
    'api.regencies.destroy',

    // API Districts
    'api.districts.index',
    'api.districts.store',
    'api.districts.show',
    'api.districts.update',
    'api.districts.destroy',

    // API Villages
    'api.villages.index',
    'api.villages.store',
    'api.villages.show',
    'api.villages.update',
    'api.villages.destroy',
  ];

  /**
   * An associative array that maps permission names to their corresponding permission category IDs.
   * This is used to associate each permission with a specific category when creating the permissions.
   */
  protected array $permissionCategoryId = [
    'api.users' => 1,
    'api.roles' => 2,
    'api.provinces' => 3,
    'api.regencies' => 4,
    'api.districts' => 5,
    'api.villages' => 6,
  ];

  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // reset cahced roles and permission
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $this->command->warn(PHP_EOL . 'Creating permissions...');
    $items = SeederProgressBar::withProgressBar(
      $this->command,
      count($this->permissions),
      function () {
        static $index = 0;
        $permission = $this->permissions[$index++];

        return collect([[
          'uuid' => Str::uuid(),
          'name' => $permission,
          'permission_category_id' => $this->permissionCategoryId[explode('.', $permission)[0] . '.' . explode('.', $permission)[1]],
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
