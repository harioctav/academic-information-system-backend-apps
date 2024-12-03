<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
  /**
   * Defines an array of user data to be seeded into the database.
   * Each element in the array represents a user with a name, email, and status.
   */
  protected array $users = [
    [
      'name' => 'Administrator',
      'email' => 'admin@example.com',
      'status' => true,
      'role' => UserRole::SuperAdmin->value
    ],
    [
      'name' => 'User PPDB',
      'email' => 'user.ppdb@example.com',
      'status' => true,
      'role' => UserRole::StudentRegisTeam->value
    ],
    [
      'name' => 'User Registration Subject',
      'email' => 'user.regis@example.com',
      'status' => true,
      'role' => UserRole::SubjectRegisTeam->value
    ],
  ];

  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating users...');
    foreach ($this->users as $data) {
      $user = User::create([
        'uuid' => Str::uuid(),
        'name' => $data['name'],
        'email' => $data['email'],
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
        'status' => $data['status']
      ]);

      $user->assignRole($data['role']);
      $this->command->info("Created user: {$data['name']} with role: {$data['role']}");
    }

    $this->command->info('Users created successfully!');
  }
}
