<?php

namespace Database\Seeders;

use App\Helpers\SeederProgressBar;
use App\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class VillageSeeder extends Seeder
{
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating villages...');

    $json = File::get(public_path('assets/json/villages.json'));
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
            'district_id' => $currentItem['district_id'],
            'name' => $currentItem['name'],
            'code' => $currentItem['code'],
            'full_code' => $currentItem['full_code'],
            'pos_code' => $currentItem['pos_code'],
            'created_at' => now(),
            'updated_at' => now(),
          ]]);
        }
      );

      Village::insert($items->toArray());
    }

    $this->command->info('Villages created successfully!');
  }
}
