<?php

namespace Database\Seeders;

use App\Enums\Evaluations\GradeType;
use App\Helpers\SeederProgressBar;
use App\Models\Grade;
use App\Models\Student;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GradeSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating Grades data...');
    $totalData = 100;
    $chunkSize = 100;

    $faker = Factory::create('id_ID');
    DB::beginTransaction();

    try {
      $students = Student::with(['major.subjects'])->get();
      $uniqueCombinations = [];

      SeederProgressBar::withProgressBar($this->command, $totalData, function () use ($faker, $totalData, $students, $chunkSize, &$uniqueCombinations) {
        $grades = [];
        $count = 0;

        while ($count < $totalData) {
          foreach ($students as $student) {
            if ($count >= $totalData) break;

            // Get random subject that hasn't been used for this student
            $availableSubjects = $student->major->subjects->filter(function ($subject) use ($student, $uniqueCombinations) {
              $key = "{$student->id}-{$subject->id}";
              return !isset($uniqueCombinations[$key]);
            });

            if ($availableSubjects->isEmpty()) continue;

            $subject = $availableSubjects->random();

            // Create unique key using student and subject only
            $key = "{$student->id}-{$subject->id}";
            $uniqueCombinations[$key] = true;

            $grades[] = [
              'uuid' => Str::uuid(),
              'student_id' => $student->id,
              'subject_id' => $subject->id,
              'grade' => $faker->randomElement(array_column(GradeType::cases(), 'value')),
              'quality' => $faker->randomFloat(2, 0, 4),
              'mutu' => $faker->randomFloat(2, 0, 4),
              'exam_period' => $faker->randomElement(['2023/2024 GANJIL', '2023/2024 GENAP']),
              'grade_note' => $faker->sentence(),
              'created_at' => now(),
              'updated_at' => now()
            ];

            $count++;

            if (count($grades) >= $chunkSize) {
              Grade::insert($grades);
              $grades = [];
            }
          }
        }

        // Insert remaining records
        if (!empty($grades)) {
          Grade::insert($grades);
        }

        return collect($grades);
      });

      DB::commit();
      $this->command->info('Grades created successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      $this->command->error($e->getMessage());
    }
  }
}
