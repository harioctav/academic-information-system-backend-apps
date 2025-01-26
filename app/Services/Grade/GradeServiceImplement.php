<?php

namespace App\Services\Grade;

use App\Enums\Evaluations\GradeType;
use App\Enums\Evaluations\RecommendationNote;
use App\Enums\WhereOperator;
use App\Helpers\Helper;
use App\Http\Resources\Evaluations\GradeResource;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Grade\GradeRepository;
use App\Repositories\Major\MajorRepository;
use App\Repositories\Recommendation\RecommendationRepository;
use App\Repositories\Student\StudentRepository;
use App\Repositories\Subject\SubjectRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GradeServiceImplement extends ServiceApi implements GradeService
{
  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Nilai";
  protected string $create_message = "Data Nilai berhasil dibuat";
  protected string $update_message = "Data Nilai berhasil diperbarui";
  protected string $delete_message = "Data Nilai berhasil dihapus";

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected GradeRepository $mainRepository;
  protected SubjectRepository $subjectRepository;
  protected StudentRepository $studentRepository;
  protected RecommendationRepository $recommendationRepository;
  protected MajorRepository $majorRepository;

  public function __construct(
    GradeRepository $mainRepository,
    SubjectRepository $subjectRepository,
    StudentRepository $studentRepository,
    RecommendationRepository $recommendationRepository,
    MajorRepository $majorRepository,
  ) {
    $this->mainRepository = $mainRepository;
    $this->subjectRepository = $subjectRepository;
    $this->studentRepository = $studentRepository;
    $this->recommendationRepository = $recommendationRepository;
    $this->majorRepository = $majorRepository;
  }

  public function query()
  {
    return $this->mainRepository->query();
  }

  public function getWhere(
    $wheres = [],
    $columns = '*',
    $comparisons = '=',
    $orderBy = null,
    $orderByType = null
  ) {
    return $this->mainRepository->getWhere(
      wheres: $wheres,
      columns: $columns,
      comparisons: $comparisons,
      orderBy: $orderBy,
      orderByType: $orderByType
    );
  }

  public function handleStore($request, \App\Models\Student $student)
  {
    try {
      DB::beginTransaction();

      /** Get Payload */
      $payload = $request->validated();

      foreach ($payload['subjects'] as $subjectId):
        /** Find Subject By Ids */
        $subject = $this->subjectRepository->findOrFail($subjectId);

        /** Recommendation Data by Subject & Student Ids */
        $recommendation = $this->recommendationRepository->getWhere(
          wheres: [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
          ]
        )->first();

        if ($payload['grade'] == GradeType::E->value):
          $recommendation->update([
            'recommendation_note' => RecommendationNote::PerluPerbaikan->value,
          ]);
          $gradeNote = "Nilai perlu perbaikan.";
        else:
          $recommendation->update([
            'recommendation_note' => RecommendationNote::Lulus->value,
          ]);
          $gradeNote = "Nilai sudah memenuhi standar kelulusan.";
        endif;

        // Tambahkan Nilai Mutu Mahasiswa
        $quality = Helper::generateQuality($payload['grade']);

        $gradeData = [
          'student_id' => $payload['student_id'],
          'subject_id' => $subject->id,
          'grade' => $payload['grade'],
          'mutu' => $payload['mutu'],
          'exam_period' => $recommendation->exam_period,
          'quality' => $quality,
          'grade_note' => $gradeNote,
        ];

        $grade = $this->mainRepository->create($gradeData);
      endforeach;

      DB::commit();

      return $this->setMessage($this->create_message)
        ->setData(
          new GradeResource($grade)
        )
        ->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleUpdate($request, \App\Models\Grade $grade)
  {
    try {
      DB::beginTransaction();

      /** Get Payload */
      $payload = $request->validated();

      /** Get Student Data */
      $student = $this->studentRepository->findOrFail($grade->student->id);

      foreach ($payload['subjects'] as $subjectId):
        /** Get Recommendation Data */
        $recommendation = $this->recommendationRepository->getWhere(
          wheres: [
            'student_id' => $student->id,
            'subject_id' => $subjectId,
          ]
        )->first();

        if (
          $grade->grade == GradeType::E->value ||
          ($grade->grade !== GradeType::E->value && $recommendation->note === RecommendationNote::RequestPerbaikan->value)
        ) {
          $newRecommendationNote = RecommendationNote::SudahDiperbaiki->value;
          $payload['grade_note'] = "Perbaikan nilai dari {$grade->grade} menjadi {$payload['grade']}.";
        } else {
          $newRecommendationNote = RecommendationNote::Lulus->value;
          $payload['grade_note'] = "Nilai sudah memenuhi standar kelulusan";
        }

        if ($payload['grade'] == GradeType::E->value) {
          $newRecommendationNote = RecommendationNote::PerluPerbaikan->value;
          $payload['grade_note'] = "Nilai perlu perbaikan";
        }

        /** Update Recommendation Note Data */
        $recommendation->update(['recommendation_note' => $newRecommendationNote]);

        /** Tambahkan Nilai Mutu Mahasiswa */
        $quality = Helper::generateQuality($payload['grade']);
        $payload['quality'] = $quality;

        $grade->update($payload);
      endforeach;

      DB::commit();

      return $this->setMessage($this->update_message)
        ->setData(
          new GradeResource($grade)
        )
        ->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleDelete(\App\Models\Grade $grade)
  {
    try {
      DB::beginTransaction();

      $this->updateRecommendationNoteWhenDeleteData($grade);
      $grade->delete();

      DB::commit();

      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleBulkDelete(array $uuid)
  {
    DB::beginTransaction();

    try {
      /**
       * Retrieves a collection of Grade models based on the provided UUIDs.
       *
       * @param array $uuid The UUIDs to filter the Grade models by.
       * @return \Illuminate\Database\Eloquent\Collection The collection of Grade models.
       */
      $grades = $this->getWhere(
        wheres: [
          'uuid' => [
            'operator' => WhereOperator::In->value,
            'value' => $uuid
          ]
        ]
      )->get();

      $deleted = 0;

      foreach ($grades as $grade) {
        $this->updateRecommendationNoteWhenDeleteData($grade);
        $grade->delete();
        $deleted++;
      }

      DB::commit();

      /**
       * Returns a JSON response with a success message after deleting the Grade model.
       *
       * @return \Illuminate\Http\JsonResponse The JSON response.
       */
      return $this->setMessage("Berhasil menghapus {$deleted} Data {$this->title}")->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  private function updateRecommendationNoteWhenDeleteData(\App\Models\Grade $grade)
  {
    $recommendation = $this->recommendationRepository->getWhere([
      'student_id' => $grade->student_id,
      'subject_id' => $grade->subject_id,
    ])->first();

    if ($this->shouldUpdateToRecommended($recommendation, $grade)) {
      $recommendation->update([
        'recommendation_note' => RecommendationNote::Direkomendasikan->value
      ]);
    }
  }

  private function shouldUpdateToRecommended(?\App\Models\Recommendation $recommendation, \App\Models\Grade $grade): bool
  {
    if (!$recommendation || !$recommendation->recommendation_note) {
      return false;
    }

    return $recommendation->recommendation_note->value === RecommendationNote::Lulus->value
      || $recommendation->recommendation_note->value === RecommendationNote::SudahDiperbaiki->value
      || $grade->grade === GradeType::E->value;
  }
}
