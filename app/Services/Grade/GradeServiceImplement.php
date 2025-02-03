<?php

namespace App\Services\Grade;

use App\Enums\Evaluations\GradeType;
use App\Enums\Evaluations\RecommendationNote;
use App\Enums\WhereOperator;
use App\Helpers\Helper;
use App\Http\Resources\Evaluations\GradeResource;
use App\Imports\GradeImport;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Grade\GradeRepository;
use App\Repositories\Major\MajorRepository;
use App\Repositories\Recommendation\RecommendationRepository;
use App\Repositories\Student\StudentRepository;
use App\Repositories\Subject\SubjectRepository;
use App\Services\Student\StudentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

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
  protected StudentService $studentService;
  protected RecommendationRepository $recommendationRepository;
  protected MajorRepository $majorRepository;

  public function __construct(
    GradeRepository $mainRepository,
    SubjectRepository $subjectRepository,
    StudentRepository $studentRepository,
    RecommendationRepository $recommendationRepository,
    MajorRepository $majorRepository,
    StudentService $studentService
  ) {
    $this->mainRepository = $mainRepository;
    $this->subjectRepository = $subjectRepository;
    $this->studentRepository = $studentRepository;
    $this->recommendationRepository = $recommendationRepository;
    $this->majorRepository = $majorRepository;
    $this->studentService = $studentService;
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

  public function handleExport(\App\Models\Student $student)
  {
    try {
      /** Get Data Student w/ Prodi + Subjects */
      $student->with([
        'grades',
        'major.subjects',
        'domicileAddress'
      ]);

      /** Group subjects by semester and check if they have grades */
      $groupedSubjects = $student->major->subjects->mapToGroups(function ($subject) use ($student) {
        $grade = $student->grades->firstWhere('subject_id', $subject->id);
        $semester = $subject->pivot->semester;

        $subject->course_credit = ($subject->course_credit === '') ? 0 : $subject->course_credit;

        return [$semester => [
          'subject' => $subject,
          'has_grade' => !is_null($grade),
          'grade' => $grade,
          'mutu' => $grade ? $grade->mutuLabel : null,
          'exam_period' => $grade ? $grade->exam_period : null,
        ]];
      });

      $formattedDate = now()->locale('id')->isoFormat('D-MM-YYYY');
      $sanitizedName = Str::upper(Str::slug($student->name, '-'));
      $full_name_of_file = "transcripts/{$formattedDate}/{$sanitizedName}/TRANSCRIPT-SEMENTARA.pdf";

      $academicInfo = $this->studentService->getAcademicInfo($student);

      // Generate PDF using a PDF library like DomPDF
      $pdf = Pdf::loadView('pdfs.transcript', [
        'student' => $student,
        'groupedSubjects' => $groupedSubjects,
        'academicInfo' => $academicInfo
      ]);

      // Store PDF temporarily
      Storage::put($full_name_of_file, $pdf->output());

      // Get the full URL for the stored PDF
      $url = Storage::url($full_name_of_file);

      return $this->setMessage("Berhasil membuat PDF Nilai Atas Nama {$student->name} ({$student->nim}).")
        ->setData(['pdf_url' => $url])->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleImport($request, \App\Models\Student $student)
  {
    DB::beginTransaction();
    try {
      if ($request->hasFile('file') && $request->file('file')->isValid()) {
        $import = new GradeImport();
        Excel::import($import, $request->file('file'));

        // Get import results
        $errors = $import->getErrors();
        if (!empty($errors)) {
          return Response::json($errors, HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $nim = $import->getNim();
        $majorName = $import->getMajor();
        $courses = $import->getCourses();

        // Validate student and major
        $major = $this->majorRepository->findOrFail($student->major->id);

        if ($nim !== $student->nim || strtolower($majorName) !== strtolower($major->name)) {
          return Response::json([
            'message' => 'Import File Gagal.',
            'errors' => [
              'file' => [
                "Nim atau Program Studi tidak sama dengan data {$student->name}."
              ]
            ]
          ], HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Get major subjects
        $majorSubjects = $major->subjects->pluck('id', 'code')->toArray();
        $existingGrades = $student->grades()->pluck('subject_id')->toArray();

        $duplicateSubjects = [];
        $newGrades = [];
        $newRecommendations = [];

        foreach ($courses as $semester => $semesterCourses):
          foreach ($semesterCourses as $course):
            $code = trim($course['kode_matakuliah']);
            $subjectName = trim($course['matakuliah']);
            $grade = trim($course['nilai']);
            $examPeriod = trim($course['masa_ujian']);

            if (!isset($majorSubjects[$code])) {
              return Response::json([
                'message' => 'Import File Gagal.',
                'errors' => [
                  'file' => [
                    "Matakuliah dengan kode {$code} tidak ditemukan dalam daftar matakuliah jurusan {$major->name}."
                  ]
                ]
              ], HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            $subjectId = $majorSubjects[$code];
            if (in_array($subjectId, $existingGrades)) {
              $duplicateSubjects[] = "{$code} - {$subjectName}";
              continue;
            }

            // Build recommendations and grades arrays
            $recommendationNote = ($grade === GradeType::E->value)
              ? RecommendationNote::PerluPerbaikan->value
              : RecommendationNote::Lulus->value;

            $gradeNote = ($grade === GradeType::E->value)
              ? "Nilai perlu dilakukan direkomendasikan ulang dan diperbaiki"
              : "Nilai sudah memenuhi standar kelulusan matakuliah";

            $newRecommendations[] = [
              'uuid' => Str::uuid(),
              'student_id' => $student->id,
              'subject_id' => $subjectId,
              'semester' => $semester,
              'exam_period' => $examPeriod,
              'recommendation_note' => $recommendationNote,
              'created_at' => now(),
              'updated_at' => now(),
            ];

            $newGrades[] = [
              'uuid' => Str::uuid(),
              'student_id' => $student->id,
              'subject_id' => $subjectId,
              'grade' => $grade ?? null,
              'quality' => Helper::generateQuality($grade),
              'mutu' => $course['nilai_mutu'] ?? null,
              'exam_period' => $examPeriod ?? null,
              'grade_note' => $gradeNote,
              'created_at' => now(),
              'updated_at' => now(),
            ];
          endforeach;
        endforeach;
        if (!empty($newGrades)) {
          DB::table('recommendations')->insert($newRecommendations);
          DB::table('grades')->insert($newGrades);
        }

        DB::commit();

        $response = [
          'duplicate_subjects' => $duplicateSubjects,
          'imported_count' => count($newGrades)
        ];

        return $this->setMessage("Berhasil Import Nilai atas nama: {$student->name} ({$student->nim})")
          ->setData($response)
          ->toJson();
      }

      return $this->setMessage('File tidak valid')->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      return $this->setCode(500)
        ->setError($e->getMessage())
        ->toJson();
    }
  }
}
