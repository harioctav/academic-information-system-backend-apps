<?php

namespace App\Services\Grade;

use App\Enums\Evaluations\GradeType;
use App\Enums\Evaluations\RecommendationNote;
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
      Log::error('Error in handleStore:', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);
      $this->exceptionResponse($e);
      return null;
    }
  }
}
