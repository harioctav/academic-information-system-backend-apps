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
      'name' => 'Administrator',
      'email' => 'scscendekia@gmail.com',
      'role' => UserRole::SuperAdmin,
    ],
    [
      'name' => 'Aldiama Hari Octavian',
      'email' => 'aldiama.octavian@gmail.com',
      'role' => UserRole::SuperAdmin,
      'phone' => '6285798888733'
    ],
    [
      'name' => 'Subject Registration Team',
      'email' => 'scscendekia.registrasi@gmail.com',
      'role' => UserRole::SubjectRegisTeam,
    ],
    [
      'name' => 'Finance Team',
      'email' => 'scscendekia.keuangan@gmail.com',
      'role' => UserRole::FinanceTeam,
    ],
    [
      'name' => 'Student Registration Team',
      'email' => 'scscendekia.studentregis@gmail.com',
      'role' => UserRole::StudentRegisTeam,
    ],
    [
      'name' => 'Filing Team',
      'email' => 'scscendekia.pemberkasan@gmail.com',
      'role' => UserRole::FilingTeam,
    ],
  ];

  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating users...');

    SeederProgressBar::withProgressBar(
      $this->command,
      count($this->users),
      function () {
        static $index = 0;
        $userData = $this->users[$index++];

        $attributes = [
          'uuid' => Str::uuid(),
          'name' => $userData['name'],
          'email' => $userData['email'],
          'email_verified_at' => now(),
          'password' => Hash::make(Helper::DefaultPassword),
          'created_at' => now(),
          'updated_at' => now(),
        ];

        // Add phone if exists
        if (isset($userData['phone'])) {
          $attributes['phone'] = $userData['phone'];
        }

        $user = User::create($attributes);
        $user->assignRole($userData['role']);

        return collect([$user]);
      }
    );

    $this->command->info('Users created successfully!');
  }
}
