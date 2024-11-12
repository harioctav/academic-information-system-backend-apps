<?php

namespace Database\Seeders;

use App\Helpers\SeederProgressBar;
use App\Models\District;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DistrictSeeder extends Seeder
{
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating districts...');

    $json = File::get(public_path('assets/json/districts.json'));
    $data = json_decode($json, true);
    $chunks = array_chunk($data, 1000);

    foreach ($chunks as $chunk) {
      $items = SeederProgressBar::withProgressBar(
        $this->command,
        count($chunk),
        function () use (&$chunk) {
          static $index = 0;
          $currentItem = $chunk[$index];
          $index++;

          return collect([[
            'id' => $currentItem['id'],
            'uuid' => Str::uuid(),
            'regency_id' => $currentItem['regency_id'],
            'code' => $currentItem['code'],
            'name' => $currentItem['name'],
            'full_code' => $currentItem['full_code'],
            'created_at' => now(),
            'updated_at' => now(),
          ]]);
        }
      );

      District::insert($items->toArray());
    }

    $this->command->info('Districts created successfully!');
  }
}
