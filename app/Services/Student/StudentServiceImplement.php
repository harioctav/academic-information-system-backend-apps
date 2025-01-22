<?php

namespace App\Services\Student;

use App\Enums\Academics\SubjectNote;
use App\Enums\Evaluations\GradeType;
use App\Enums\Evaluations\RecommendationNote;
use App\Enums\WhereOperator;
use App\Helpers\Helper;
use App\Http\Resources\Academics\StudentResource;
use App\Repositories\Grade\GradeRepository;
use App\Repositories\Major\MajorRepository;
use App\Repositories\Recommendation\RecommendationRepository;
use App\Traits\FileUpload;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Student\StudentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentServiceImplement extends ServiceApi implements StudentService
{
  use FileUpload;

  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Mahasiswa";

  protected string $create_message = "Data Mahasiswa berhasil dibuat";

  protected string $update_message = "Data Mahasiswa berhasil diperbarui";

  protected string $delete_message = "Data Mahasiswa berhasil dihapus";

  protected string $delete_image_message = "Foto Mahasiswa berhasil dihapus";


  protected MajorRepository $majorRepository;
  protected StudentRepository $mainRepository;
  protected RecommendationRepository $recommendationRepository;
  protected GradeRepository $gradeRepository;

  public function __construct(
    MajorRepository $majorRepository,
    StudentRepository $mainRepository,
    RecommendationRepository $recommendationRepository,
    GradeRepository $gradeRepository
  ) {
    $this->mainRepository = $mainRepository;
    $this->majorRepository = $majorRepository;
    $this->recommendationRepository = $recommendationRepository;
    $this->gradeRepository = $gradeRepository;
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

  public function getAcademicInfo(\App\Models\Student $student)
  {
    try {
      $allSubjects = $student->major->subjects;
      $totalCourseCredit = $this->countTotalCourseCredit($allSubjects);

      // Filter Rekomendasi
      $statusFilters = [
        'passed' => [
          RecommendationNote::Lulus->value,
          RecommendationNote::SudahDiperbaiki->value,
        ],
        'ongoing' => [
          RecommendationNote::Direkomendasikan->value,
          RecommendationNote::SemesterBerjalan->value,
        ],
        'improvement' => [
          RecommendationNote::PerluPerbaikan->value,
        ],
      ];

      // Menghitung informasi kredit dan nilai mutu
      $creditInfo = $this->calculateCreditInfo($student->id, $statusFilters);

      // Perhitungan IPK
      $gpa = Helper::calculateGPA($student->id);
      $percentage = ($gpa / 4) * 100;

      // Menghitung SKS yang benar-benar sudah lulus (tidak termasuk 'Perlu Perbaikan')
      $actualPassedCredits = $creditInfo['passed'];

      // Menghitung estimasi sisa semester
      $remainingSemesters = $this->estimateRemainingSemesters($actualPassedCredits, $totalCourseCredit);

      // Get recommended subjects for the student
      $recommendationByStudentId = $this->recommendationRepository->getWhere(
        wheres: [
          'student_id' => $student->id
        ]
      )->pluck('subject_id');

      // Simpan base query
      $baseQuery = $this->gradeRepository->getWhere(
        wheres: [
          'student_id' => $student->id
        ]
      );

      // Clone query untuk mutu
      $mutu = (clone $baseQuery)
        ->whereIn('subject_id', $recommendationByStudentId)
        ->where('grade', '!=', GradeType::E->value)
        ->sum('mutu');

      // Clone query untuk cek grade E
      $hasGradeE = (clone $baseQuery)
        ->where('grade', GradeType::E->value)
        ->exists();

      return [
        'credit_has_been_taken' => $creditInfo['total'], // total SKS yang sudah ditempuh atau di ambil
        'credit_has_been_passed' => $creditInfo['passed'], // Total SKS yang sudah lulus
        'credit_being_taken' => $creditInfo['ongoing'], // Total SKS yang sedang diambil
        'credit_need_improvement' => $creditInfo['improvement'], // Total SKS yang perlu perbaikan atau dalam perbaikan
        'transfer_credit' => $creditInfo['transfer'], // alih kredit
        'credit_by_curriculum' => $creditInfo['curriculum'], // berdasarkan kurikulum
        'total_credit_not_yet_taken' => $totalCourseCredit - $creditInfo['total'], // total sks yang belum di tempuh
        'total_credit_not_yet_taken_by_passed' => $totalCourseCredit - $creditInfo['passed'], // total sks yang belum ditempuh berdasarkan kelulusan
        'total_course_credit' => $totalCourseCredit, // total sks wajib tempuh
        'gpa' => $gpa,
        'percentage' => $percentage,
        'estimated_remaining_semesters' => $remainingSemesters,
        'has_grade_e' => $hasGradeE,
        'mutu' => rtrim(rtrim(number_format($mutu, 2), '0'), '.'), // Total nilai mutu
      ];
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }

  private function estimateRemainingSemesters($passedCredits, $totalCredits)
  {
    $remainingCredits = $totalCredits - $passedCredits;
    if ($remainingCredits <= 0) {
      return 0;
    }

    $semesters = 0;
    $creditsPerSemester = [20, 20]; // Semester 1 dan 2

    while ($remainingCredits > 0) {
      $semesters++;
      $maxCredits = ($semesters <= 2) ? $creditsPerSemester[$semesters - 1] : 24;
      $remainingCredits -= min($remainingCredits, $maxCredits);
    }

    return $semesters;
  }

  private function calculateCreditInfo($studentId, $statusFilters)
  {
    $query = $this->recommendationRepository->query()
      ->where('student_id', $studentId)
      ->join('subjects', 'recommendations.subject_id', '=', 'subjects.id')
      ->select('recommendations.recommendation_note', 'recommendations.exam_period', 'subjects.course_credit');

    $results = $query->get();

    $creditInfo = [
      'total' => 0,
      'passed' => 0,
      'ongoing' => 0,
      'improvement' => 0,
      'transfer' => 0,
      'curriculum' => 0,
    ];

    foreach ($results as $result) {
      $credit = $result->course_credit;
      $creditInfo['total'] += $credit;

      if ($result->exam_period == '55555') {
        $creditInfo['transfer'] += $credit;
      } else {
        $creditInfo['curriculum'] += $credit;
      }

      foreach ($statusFilters as $key => $statuses) {
        if (in_array($result->recommendation_note, $statuses)) {
          $creditInfo[$key] += $credit;
          break;
        }
      }
    }

    // Tidak perlu mengurangi $creditInfo['improvement'] dari $creditInfo['passed']

    return $creditInfo;
  }

  private function countTotalCourseCredit($subjects)
  {
    $totalCourseCredit = 0;
    $subjectsBySemester = $subjects->groupBy('pivot.semester');

    foreach ($subjectsBySemester as $semester => $subjects) {
      // Pisahkan mata kuliah berdasarkan "PILIH SALAH SATU"
      $withPilihSalahSatu = $subjects->filter(function ($subject) {
        return str_contains($subject->subject_note, SubjectNote::PS->value);
      });

      $withoutPilihSalahSatu = $subjects->filter(function ($subject) {
        return !str_contains($subject->subject_note, SubjectNote::PS->value);
      });

      // Tambahkan total SKS dari mata kuliah tanpa "PILIH SALAH SATU"
      foreach ($withoutPilihSalahSatu as $subject) {
        $totalCourseCredit += $subject->course_credit; // Mengambil SKS dari kolom course_credit di tabel subjects
      }

      // Jika ada mata kuliah "PILIH SALAH SATU", hanya tambahkan salah satu dari grup ini
      if ($withPilihSalahSatu->isNotEmpty()) {
        $totalCourseCredit += $withPilihSalahSatu->max()->course_credit; // Ambil salah satu SKS dari mata kuliah pilihan
        // $totalCourseCredit += $withPilihSalahSatu->first()->course_credit; // Ambil salah satu SKS dari mata kuliah pilihan
      }
    }

    return $totalCourseCredit;
  }

  public function handleStore($request)
  {
    try {
      DB::beginTransaction();
      $payload = $request->validated();

      $major = $this->majorRepository->findOrFail(
        (int) $payload['major']
      );

      // Handle Photo if exists
      $imagePath = null;

      if ($request->hasFile('student_photo_path')) {
        $result = $this->upload(
          file: $request->file('student_photo_path'),
          path: 'images/students'
        );

        if ($result['success']) {
          $imagePath = $result['path'];
        }
      }

      $payload['student_photo_path'] = $imagePath;
      $payload['major_id'] = $major->id;

      // Save to database
      $created = $this->mainRepository->create($payload);

      // Create student addresses
      if (!empty($payload['addresses'])) {
        foreach ($payload['addresses'] as $address) {
          $created->addresses()->create([
            'uuid' => Str::uuid(),
            'type' => $address['type'],
            'village_id' => $address['village_id'],
            'address' => $address['address']
          ]);
        }
      }

      DB::commit();

      return $this->setMessage($this->create_message)
        ->setData(
          new StudentResource($created->load([
            'domicileAddress.village',
            'idCardAddress.village'
          ]))
        )
        ->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleUpdate($request, \App\Models\Student $student)
  {
    try {
      DB::beginTransaction();
      $payload = $request->validated();

      $major = $this->majorRepository->findOrFail(
        (int) $payload['major']
      );

      // Handle Photo if exists
      if ($request->hasFile('student_photo_path')) {
        $result = $this->upload(
          file: $request->file('student_photo_path'),
          path: 'images/students',
          currentFile: $student->student_photo_path
        );

        if ($result['success']) {
          $payload['student_photo_path'] = $result['path'];
        }
      } else {
        // Keep existing photo if no new photo is uploaded
        $payload['student_photo_path'] = $student->student_photo_path;
      }

      $payload['major_id'] = $major->id;

      // Update student data
      $student->update($payload);

      // Update addresses
      if (!empty($payload['addresses'])) {
        // Delete existing addresses
        $student->addresses()->delete();

        // Create new addresses
        foreach ($payload['addresses'] as $address) {
          $student->addresses()->create([
            'uuid' => Str::uuid(),
            'type' => $address['type'],
            'village_id' => $address['village_id'],
            'address' => $address['address']
          ]);
        }
      }

      DB::commit();

      return $this->setMessage($this->update_message)
        ->setData(
          new StudentResource($student->load([
            'domicileAddress.village',
            'idCardAddress.village'
          ]))
        )
        ->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleDestroy(\App\Models\Student $student)
  {
    try {

      DB::beginTransaction();

      // Handle delete file
      if ($student->student_photo_path) {
        Storage::delete($student->student_photo_path);
      }

      $this->mainRepository->delete($student->id);

      DB::commit();

      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleBulkDelete(array $uuid)
  {
    try {
      DB::beginTransaction();
      $students = $this->getWhere(
        wheres: [
          'uuid' => [
            'operator' => WhereOperator::In->value,
            'value' => $uuid
          ]
        ]
      )->get();

      $deletedCount = 0;
      foreach ($students as $student) {
        if ($student->student_photo_path) {
          Storage::delete($student->student_photo_path);
        }
        $student->delete();
        $deletedCount++;
      }

      DB::commit();

      return $this->setMessage("Berhasil menghapus {$deletedCount} Data Mahasiswa")->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleDeleteImage(\App\Models\Student $student)
  {
    try {
      DB::beginTransaction();

      // Handle delete file
      if ($student->student_photo_path) {
        Storage::delete($student->student_photo_path);
      }

      $student->update([
        'student_photo_path' => null,
      ]);

      DB::commit();

      return $this->setMessage($this->delete_image_message)->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      $this->exceptionResponse($e);
      return null;
    }
  }
}
