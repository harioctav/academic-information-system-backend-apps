<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Helpers\Helper;
use App\Helpers\SeederProgressBar;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
  /**
   * An array of user data to be seeded in the database.
   */
  protected array $users = [
    [
      'name' => 'Super Admin',
      'email' => 'superadmin@example.com',
      'role' => UserRole::SuperAdmin,
    ],
    [
      'name' => 'Subject Registration Team',
      'email' => 'subject.regis@example.com',
      'role' => UserRole::SubjectRegisTeam,
    ],
    [
      'name' => 'Finance Team',
      'email' => 'finance@example.com',
      'role' => UserRole::FinanceTeam,
    ],
    [
      'name' => 'Student Registration Team',
      'email' => 'student.regis@example.com',
      'role' => UserRole::StudentRegisTeam,
    ],
    [
      'name' => 'Filing Team',
      'email' => 'filing@example.com',
      'role' => UserRole::FilingTeam,
    ],
  ];

  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating users...');

    $items = SeederProgressBar::withProgressBar(
      $this->command,
      count($this->users),
      function () {
        static $index = 0;
        $userData = $this->users[$index++];

        $user = User::create([
          'uuid' => Str::uuid(),
          'name' => $userData['name'],
          'email' => $userData['email'],
          'email_verified_at' => now(),
          'password' => Hash::make(Helper::DefaultPassword),
          'created_at' => now(),
          'updated_at' => now(),
        ]);

        $user->assignRole($userData['role']);

        return collect([$user]);
      }
    );

    $this->command->info('Users created successfully!');
  }
}
