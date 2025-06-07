<?php

namespace Database\Seeders;

use App\Helpers\SeederProgressBar;
use App\Models\PermissionCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionCategorySeeder extends Seeder
{
  /**
   * An array of permission category names to be seeded in the database.
   */
  protected array $categories = [
    'users.name',
    'roles.name',
    'provinces.name',
    'regencies.name',
    'districts.name',
    'villages.name',
    'majors.name',
    'subjects.name',
    'students.name',
    'grades.name',
    'recommendations.name',
    'billings.name'
  ];

  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating permission categories...');
    $items = SeederProgressBar::withProgressBar(
      $this->command,
      count($this->categories),
      function () {
        static $index = 0;

        return collect([[
          'uuid' => Str::uuid(),
          'name' => $this->categories[$index++],
          'created_at' => now(),
          'updated_at' => now(),
        ]]);
      }
    );

    PermissionCategory::insert($items->toArray());
    $this->command->info('Permission categories created successfully!');
  }
}
