<?php

namespace Database\Seeders;

use App\Enums\Evaluations\RecommendationNote;
use App\Helpers\SeederProgressBar;
use App\Models\Recommendation;
use App\Models\Student;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecommendationSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $this->command->warn(PHP_EOL . 'Creating Recommendations data...');
    $totalData = 100;
    $chunkSize = 100;

    $faker = Factory::create('id_ID');
    DB::beginTransaction();

    try {
      $students = Student::with(['major.subjects'])->get();
      $uniqueCombinations = [];

      SeederProgressBar::withProgressBar($this->command, $totalData, function () use ($faker, $totalData, $students, $chunkSize, &$uniqueCombinations) {
        $recommendations = [];
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
            $semester = $subject->majors->where('id', $student->major_id)->first()->pivot->semester;

            // Create unique key using student and subject only
            $key = "{$student->id}-{$subject->id}";
            $uniqueCombinations[$key] = true;

            $recommendations[] = [
              'uuid' => Str::uuid(),
              'student_id' => $student->id,
              'subject_id' => $subject->id,
              'semester' => $semester,
              'exam_period' => $faker->randomElement(['2023/2024 GANJIL', '2023/2024 GENAP']),
              'recommendation_note' => $faker->randomElement([
                RecommendationNote::DalamPerbaikan->value,
                RecommendationNote::RequestPerbaikan->value
              ]),
              'created_at' => now(),
              'updated_at' => now()
            ];

            $count++;

            if (count($recommendations) >= $chunkSize) {
              Recommendation::insert($recommendations);
              $recommendations = [];
            }
          }
        }

        // Insert remaining records
        if (!empty($recommendations)) {
          Recommendation::insert($recommendations);
        }

        return collect($recommendations);
      });

      DB::commit();
      $this->command->info('Recommendations created successfully!');
    } catch (\Exception $e) {
      DB::rollBack();
      $this->command->error($e->getMessage());
    }
  }
}
