<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Helpers\SeederProgressBar;
use App\Models\Role;
use App\Models\User;
use App\Notifications\Settings\RolePermissionUpdated;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
  protected int $totalNotifications = 100;

  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating notifications...');

    $users = User::all();
    $roles = Role::all();
    $role4 = Role::find(4);

    $faker = Factory::create('id_ID');

    $items = SeederProgressBar::withProgressBar(
      $this->command,
      $this->totalNotifications,
      function () use ($users, $roles, $role4, $faker) {
        $randomUser = $users->random();
        $user = User::find(12);
        $selectedRole = (rand(1, 100) <= 60) ? $role4 : $roles->random();
        $randomDate = Carbon::now()->subDays(rand(0, 1095));

        $notification = $user->notifications()->create([
          'id' => Str::uuid(),
          'type' => RolePermissionUpdated::class,
          'notifiable_type' => User::class,
          'notifiable_id' => 12,
          'data' => [
            'role_id' => 4,
            'role_name' => "student_regis_team",
            'title' => 'Perubahan Hak Akses',
            'notification_type' => $faker->randomElement(["permission", "academic", "system", "subject"]),
            'message' => "Hak akses untuk peran student_regis_team telah diperbarui. Silahkan lakukan Refresh untuk memperbarui Hak Akses anda."
          ],
          'created_at' => $randomDate,
          'updated_at' => $randomDate,
        ]);

        return collect([$notification]);
      }
    );

    $this->command->info('Notifications created successfully!');
  }
}
