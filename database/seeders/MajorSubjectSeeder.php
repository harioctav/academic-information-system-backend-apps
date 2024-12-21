<?php

namespace Database\Seeders;

use App\Helpers\SeederProgressBar;
use App\Imports\MajorSubjectImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class MajorSubjectSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Importing major subjects data...');

    $importer = new MajorSubjectImport();
    $file = public_path('assets/excels/template-master-data-prodi-matakuliah.xlsx');

    SeederProgressBar::withProgressBar(
      $this->command,
      1,
      function () use ($file, $importer) {
        Excel::import($importer, $file);
        return collect([1]);
      }
    );

    // Display the feedback message from importer
    $this->command->info($importer->getFeedbackMessage());
  }
}
