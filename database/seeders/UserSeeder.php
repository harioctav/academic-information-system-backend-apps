<?php

namespace Database\Seeders;

use App\Helpers\SeederProgressBar;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
  protected array $users = [
    [
      'name' => 'Administrator',
      'email' => 'admin@example.com',
      'status' => true,
    ],
    [
      'name' => 'User Pertama',
      'email' => 'user1@example.com',
      'status' => true,
    ],
    [
      'name' => 'User Kedua',
      'email' => 'user2@example.com',
      'status' => false,
    ]
  ];

  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->warn(PHP_EOL, 'Creating users...');
    $items = SeederProgressBar::withProgressBar(
      $this->command,
      count($this->users),
      function () {
        static $index = 0;

        $user = $this->users[$index++];

        return collect([[
          'uuid' => Str::uuid(),
          'name' => $user['name'],
          'email' => $user['email'],
          'email_verified_at' => now(),
          'password' => bcrypt('password'),
          'status' => $user['status'],
          'created_at' => now(),
          'updated_at' => now(),
        ]]);
      }
    );

    User::insert($items->toArray());
    $this->command->info('Users created successfully!');
  }
}
