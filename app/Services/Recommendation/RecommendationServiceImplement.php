<?php

namespace App\Services\Recommendation;

use App\Enums\Evaluations\GradeType;
use App\Enums\Evaluations\RecommendationNote;
use App\Enums\UserRole;
use App\Enums\WhereOperator;
use App\Http\Resources\Evaluations\RecommendationResource;
use App\Notifications\Evaluations\Recommendations\RecommendationCreated;
use App\Notifications\Evaluations\Recommendations\RecommendationDeleted;
use App\Notifications\Evaluations\Recommendations\RecommendationUpdated;
use App\Repositories\Grade\GradeRepository;
use App\Repositories\Major\MajorRepository;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Recommendation\RecommendationRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Support\Facades\Auth;
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
  protected UserRepository $userRepository;

  public function __construct(
    GradeRepository $gradeRepository,
    MajorRepository $majorRepository,
    RecommendationRepository $mainRepository,
    UserRepository $userRepository
  ) {
    $this->mainRepository = $mainRepository;
    $this->gradeRepository = $gradeRepository;
    $this->majorRepository = $majorRepository;
    $this->userRepository = $userRepository;
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
      $payload = $request->validated();
      $processedRecommendations = collect(); // Initialize as Collection

      foreach ($payload['subjects'] as $subjectId) {
        if (!$processedRecommendations->contains('subject_id', $subjectId)) {
          $semester = $this->getSubjectSemester($student->major_id, $subjectId);
          $recommendation = $this->getOrCreateRecommendation($student->id, $subjectId, $semester, $payload);
          $processedRecommendations->push($recommendation);
        }
      }

      // Send notification
      $users = $this->userRepository->getUserByRelations(
        'roles',
        'name',
        [
          UserRole::SuperAdmin->value,
          UserRole::SubjectRegisTeam->value
        ]
      )->get();

      $notification = new RecommendationCreated(
        $processedRecommendations,
        $student,
        Auth::user()
      );

      $users->each(function ($user) use ($notification) {
        $user->notify($notification);
      });
      // Send notification

      DB::commit();
      return $this->setMessage($this->create_message)->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      return $this->setMessage($e->getMessage())->toJson();
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
    DB::beginTransaction();

    try {
      /**
       * Retrieves the validated request payload.
       */
      $payload = $request->validated();

      // Simpan note lama sebelum update
      $oldNote = $recommendation->recommendation_note;

      /**
       * Updates the recommendation model with the provided payload data.
       */
      $recommendation->update($payload);

      // Jika note diubah menjadi "submit_ke_web_universitas_terbuka"
      if (
        isset($payload['recommendation_note']) &&
        $payload['recommendation_note'] === RecommendationNote::Submitted->value &&
        $oldNote !== RecommendationNote::Submitted->value
      ) {

        // Kirim notifikasi ke tim keuangan
        $financeTeamUsers = $this->userRepository->getUserByRelations(
          'roles',
          'name',
          [UserRole::FinanceTeam->value]
        )->get();

        // Cara yang lebih aman untuk mendapatkan label
        if ($oldNote instanceof RecommendationNote) {
          $oldNoteLabel = $oldNote->label();
        } else {
          $oldNoteEnum = RecommendationNote::tryFrom($oldNote);
          $oldNoteLabel = $oldNoteEnum ? $oldNoteEnum->label() : $oldNote;
        }

        $newNoteLabel = RecommendationNote::Submitted->label();

        $notification = new RecommendationUpdated(
          $recommendation,
          $recommendation->student,
          Auth::user(),
          $oldNoteLabel,
          $newNoteLabel
        );

        $financeTeamUsers->each(function ($user) use ($notification) {
          $user->notify($notification);
        });
      }

      DB::commit();

      /**
       * Returns the updated recommendation as a JSON response, with a success message set.
       *
       * @return \Illuminate\Http\JsonResponse
       */
      return $this->setMessage($this->update_message)->setData(
        new RecommendationResource($recommendation)
      )->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      return $this->setMessage($e->getMessage())->toJson();
    }
  }

  public function handleDelete(\App\Models\Recommendation $recommendation)
  {
    try {
      $student = $recommendation->student;
      $recommendations = collect([$recommendation]);

      $users = $this->userRepository->getUserByRelations(
        'roles',
        'name',
        [
          UserRole::SuperAdmin->value,
          UserRole::SubjectRegisTeam->value
        ]
      )->get();

      $notification = new RecommendationDeleted(
        $recommendations,
        $student,
        Auth::user()
      );

      $users->each(function ($user) use ($notification) {
        $user->notify($notification);
      });
      // Send notification

      $recommendation->delete();

      /**
       * Returns a JSON response with a success message after deleting the Recommendation model.
       *
       * @return \Illuminate\Http\JsonResponse The JSON response.
       */
      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
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

      if ($recommendations->isNotEmpty()) {
        $student = $recommendations->first()->student;

        // Send notification
        $users = $this->userRepository->getUserByRelations(
          'roles',
          'name',
          [
            UserRole::SuperAdmin->value,
            UserRole::SubjectRegisTeam->value
          ]
        )->get();

        $notification = new RecommendationDeleted(
          $recommendations,
          $student,
          Auth::user()
        );

        $users->each(function ($user) use ($notification) {
          $user->notify($notification);
        });
        // Send notification
      }

      $deleted = 0;

      foreach ($recommendations as $recommendation) {
        $recommendation->delete();
        $deleted++;
      }

      /**
       * Returns a JSON response with a success message after deleting the Recommendation model.
       *
       * @return \Illuminate\Http\JsonResponse The JSON response.
       */
      return $this->setMessage("Berhasil menghapus {$deleted} Data {$this->title}")->toJson();
    } catch (\Exception $e) {
      return $this->setMessage($e->getMessage())->toJson();
    }
  }
}
