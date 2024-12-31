<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Helpers\Helper;
use App\Helpers\SeederProgressBar;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating Users data...');

    $faker = Factory::create('id_ID');

    DB::beginTransaction();

    // Create admin user first
    $adminUser = User::create([
      'uuid' => Str::uuid(),
      'name' => 'Administrator',
      'email' => 'admin@gmail.com',
      'email_verified_at' => now(),
      'password' => bcrypt(Helper::DefaultPassword),
      'status' => true,
      'phone' => '6285798888733'
    ]);
    $adminUser->assignRole(UserRole::SuperAdmin->value);
    $this->command->info("Created admin user: Administrator");

    // Prepare data for bulk insertion
    $userData = [];
    $chunkSize = 10;
    $totalUsers = 50;
    $chunks = array_chunk(range(1, $totalUsers), $chunkSize);

    foreach ($chunks as $chunk) {
      $items = SeederProgressBar::withProgressBar(
        $this->command,
        count($chunk),
        function () use ($faker) {
          $availableRoles = array_filter(
            UserRole::cases(),
            fn($role) => $role !== UserRole::SuperAdmin
          );

          $user = [
            'uuid' => Str::uuid(),
            'name' => $faker->name(),
            'email' => $faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt(Helper::DefaultPassword),
            'status' => $faker->boolean(80),
            'phone' => '628' . $faker->unique()->numerify('##########'),
            'created_at' => now(),
            'updated_at' => now()
          ];

          return collect([$user]);
        }
      );

      User::insert($items->toArray());

      // Assign roles to newly created users
      $lastInsertedUsers = User::latest()->take(count($chunk))->get();
      foreach ($lastInsertedUsers as $user) {
        $availableRoles = array_filter(
          UserRole::cases(),
          fn($role) => $role !== UserRole::SuperAdmin
        );
        $user->assignRole($faker->randomElement($availableRoles)->value);
      }
    }

    DB::commit();

    $this->command->info('Users created successfully!');
  }
}
