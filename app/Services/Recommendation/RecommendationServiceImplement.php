<?php

namespace App\Services\Recommendation;

use App\Enums\Evaluations\GradeType;
use App\Enums\Evaluations\RecommendationNote;
use App\Enums\WhereOperator;
use App\Repositories\Grade\GradeRepository;
use App\Repositories\Major\MajorRepository;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Recommendation\RecommendationRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RecommendationServiceImplement extends ServiceApi implements RecommendationService
{
  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Rekomendasi Matakuliah";
  protected string $create_message = "Data Rekomendasi Matakuliah berhasil dibuat";
  protected string $update_message = "Data Rekomendasi Matakuliah berhasil diperbarui";
  protected string $delete_message = "Data Rekomendasi Matakuliah berhasil dihapus";

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected GradeRepository $gradeRepository;
  protected MajorRepository $majorRepository;
  protected RecommendationRepository $mainRepository;

  public function __construct(
    GradeRepository $gradeRepository,
    MajorRepository $majorRepository,
    RecommendationRepository $mainRepository,
  ) {
    $this->mainRepository = $mainRepository;
    $this->gradeRepository = $gradeRepository;
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
    DB::beginTransaction();

    try {
      // Tangkap form yang disubmit
      $payload = $request->validated();

      // Find Student & major
      $major = $this->majorRepository->findOrFail($student->major_id);

      $processedRecommendations = [];

      foreach ($payload['subjects'] as $subjectIds) {
        if (!in_array($subjectIds, $processedRecommendations)) {
          $semester = $this->getSubjectSemester($major->id, $subjectIds);
          $this->getOrCreateRecommendation($student->id, $subjectIds, $semester, $payload);
          $processedRecommendations[] = $subjectIds; // Simpan subjectId yang sudah diproses
        }
      }

      DB::commit();

      return $this->setMessage($this->create_message)->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error in handleStore:', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      $this->exceptionResponse($e);
      return null;
    }
  }

  /**
   * Retrieves the semester for a given major and subject.
   *
   * @param int $majorId The ID of the major.
   * @param int $subjectId The ID of the subject.
   * @return int The semester for the given major and subject.
   */
  private function getSubjectSemester($majorId, $subjectId)
  {
    return DB::table('major_has_subjects')
      ->where('major_id', $majorId)
      ->where('subject_id', $subjectId)
      ->value('semester');
  }

  /**
   * Retrieves or creates a recommendation for the given student and subject.
   *
   * If a recommendation does not exist for the given student and subject, a new one is created.
   * If a recommendation already exists, it is updated based on the student's grade for the subject.
   *
   * @param int $studentId The ID of the student.
   * @param int $subjectId The ID of the subject.
   * @param int $semester The semester for the subject.
   * @param array $payload Additional data for the recommendation.
   * @return \App\Models\Recommendation The retrieved or created recommendation.
   */
  private function getOrCreateRecommendation($studentId, $subjectId, $semester, $payload)
  {
    // Cek apakah rekomendasi sudah ada berdasarkan student_id dan subject_id
    $recommendation = $this->mainRepository->getWhere([
      'student_id' => $studentId,
      'subject_id' => $subjectId,
    ])->first();

    // Jika data rekomendasi belum ada
    if (is_null($recommendation)) {
      return $this->mainRepository->create([
        'uuid' => Str::uuid(),
        'student_id' => $studentId,
        'subject_id' => (int) $subjectId,
        'semester' => $semester,
        'exam_period' => $payload['exam_period'],
        'recommendation_note' => $payload['recommendation_note']
      ]);
    } else {

      // Cek apakah subject sudah memiliki nilai
      $hasGrade = $this->getSubjectsWithEGrade($studentId, [$subjectId])->exists();

      // Jika subject nilai nya E
      if ($hasGrade) {
        $note = RecommendationNote::DalamPerbaikan->value;
      } else {
        $note = RecommendationNote::RequestPerbaikan->value;
      }

      $this->mainRepository->update($recommendation->id, ['recommendation_note' => $note]);
    }

    // Kembalikan data rekomendasi
    return $recommendation = $this->mainRepository->findOrFail($recommendation->id);
  }

  /**
   * Retrieves the subjects with an 'E' grade for the given student and subject IDs.
   *
   * @param int $studentId The ID of the student.
   * @param array $subjectIds The IDs of the subjects.
   * @return \Illuminate\Database\Eloquent\Collection The subjects with an 'E' grade.
   */
  private function getSubjectsWithEGrade($studentId, array $subjectIds)
  {
    return $this->gradeRepository->getWhere([
      'student_id' => $studentId,
      'subject_id' => [
        'operator' => 'in',
        'value' => $subjectIds
      ],
      'grade' => GradeType::E->value
    ]);
  }

  public function handleUpdate($request, \App\Models\Recommendation $recommendation)
  {
    try {
      /**
       * Retrieves the validated request payload.
       */
      $payload = $request->validated();

      /**
       * Updates the recommendation model with the provided payload data.
       */
      $recommendation->update($payload);

      /**
       * Returns the updated recommendation as a JSON response, with a success message set.
       *
       * @return \Illuminate\Http\JsonResponse
       */
      return $this->setMessage($this->update_message)->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleDelete(\App\Models\Recommendation $recommendation)
  {
    try {
      $recommendation->delete();

      /**
       * Returns a JSON response with a success message after deleting the Recommendation model.
       *
       * @return \Illuminate\Http\JsonResponse The JSON response.
       */
      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleBulkDelete(array $uuid)
  {
    try {
      /**
       * Retrieves a collection of Recommendation models based on the provided UUIDs.
       *
       * @param array $uuid The UUIDs to filter the Recommendation models by.
       * @return \Illuminate\Database\Eloquent\Collection The collection of Recommendation models.
       */
      $recommendations = $this->getWhere(
        wheres: [
          'uuid' => [
            'operator' => WhereOperator::In->value,
            'value' => $uuid
          ]
        ]
      )->get();

      foreach ($recommendations as $recommendation) {
        $recommendation->delete();
      }

      /**
       * Returns a JSON response with a success message after deleting the Recommendation model.
       *
       * @return \Illuminate\Http\JsonResponse The JSON response.
       */
      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }
}
