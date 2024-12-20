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
      'email' => 'admin@gmail.com',
      'status' => true,
      'role' => UserRole::SuperAdmin->value,
      'phone' => '6285798888733'
    ],
    [
      'name' => 'User PPDB',
      'email' => 'user.ppdb@gmail.com',
      'status' => true,
      'role' => UserRole::StudentRegisTeam->value,
      'phone' => '6285659466622'
    ],
    [
      'name' => 'User Registration Subject',
      'email' => 'user.regis@gmail.com',
      'status' => false,
      'role' => UserRole::SubjectRegisTeam->value,
      'phone' => '6285153435008'
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
        'status' => $data['status'],
        'phone' => $data['phone']
      ]);

      $user->assignRole($data['role']);
      $this->command->info("Created user: {$data['name']} with role: {$data['role']}");
    }

    $this->command->info('Users created successfully!');
  }
}
