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
      'name' => 'User Pertama',
      'email' => 'user1@example.com',
      'status' => true,
      'role' => UserRole::StudentRegisTeam->value
    ],
    [
      'name' => 'User Kedua',
      'email' => 'user2@example.com',
      'status' => false,
      'role' => UserRole::FinanceTeam->value
    ],
    [
      'name' => 'User Ketiga',
      'email' => 'user3@example.com',
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
    foreach ($this->users as $userData) {
      $user = User::create([
        'uuid' => Str::uuid(),
        'name' => $userData['name'],
        'email' => $userData['email'],
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
        'status' => $userData['status']
      ]);

      $user->assignRole($userData['role']);
      $this->command->info("Created user: {$userData['name']} with role: {$userData['role']}");
    }

    $this->command->info('Users created successfully!');
  }
}
