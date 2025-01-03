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
   * Get quality point based on grade
   */
  private function getQualityPoint(string $grade): float
  {
    return match ($grade) {
      GradeType::A->value => 4.00,
      GradeType::AMin->value => 3.70,
      GradeType::B->value => 3.00,
      GradeType::BMin->value => 2.70,
      GradeType::C->value => 2.00,
      GradeType::CMin->value => 1.70,
      GradeType::D->value => 1.00,
      GradeType::E->value => 0.00,
      default => 0.00,
    };
  }

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

            // Generate random grade
            $grade = $faker->randomElement(array_column(GradeType::cases(), 'value'));

            // Get corresponding quality point
            $qualityPoint = $this->getQualityPoint($grade);

            $grades[] = [
              'uuid' => Str::uuid(),
              'student_id' => $student->id,
              'subject_id' => $subject->id,
              'grade' => $grade,
              'quality' => $qualityPoint,
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
