<?php

namespace Database\Seeders;

use App\Helpers\SeederProgressBar;
use App\Models\Regency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RegencySeeder extends Seeder
{
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating regencies...');

    $json = File::get(public_path('assets/json/regencies.json'));
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
            'province_id' => $currentItem['province_id'],
            'type' => $currentItem['type'],
            'name' => $currentItem['name'],
            'code' => $currentItem['code'],
            'full_code' => $currentItem['full_code'],
            'created_at' => now(),
            'updated_at' => now(),
          ]]);
        }
      );

      Regency::insert($items->toArray());
    }

    $this->command->info('Regencies created successfully!');
  }
}
