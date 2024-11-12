<?php

namespace Database\Seeders;

use App\Helpers\SeederProgressBar;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProvinceSeeder extends Seeder
{
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating provinces...');

    $json = File::get(public_path('assets/json/provinces.json'));
    $data = json_decode($json, true);

    $items = SeederProgressBar::withProgressBar(
      $this->command,
      count($data),
      function () use ($data) {
        static $index = 0;
        $currentItem = $data[$index];
        $index++;

        return collect([[
          'id' => $currentItem['id'],
          'uuid' => Str::uuid(),
          'name' => $currentItem['name'],
          'code' => $currentItem['code'],
          'created_at' => now(),
          'updated_at' => now(),
        ]]);
      }
    );

    Province::insert($items->toArray());
    $this->command->info('Provinces created successfully!');
  }
}
