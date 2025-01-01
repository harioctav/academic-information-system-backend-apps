<?php

namespace App\Services\MajorSubject;

use App\Enums\WhereOperator;
use App\Http\Resources\Academics\MajorSubjectResource;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\MajorSubject\MajorSubjectRepository;
use App\Repositories\Subject\SubjectRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MajorSubjectServiceImplement extends ServiceApi implements MajorSubjectService
{
  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Matakuliah di Program Studi";

  protected string $create_message = "Data Matakuliah di Program Studi berhasil dibuat";

  protected string $update_message = "Data Matakuliah di Program Studi berhasil diperbarui";

  protected string $delete_message = "Data Matakuliah di Program Studi berhasil dihapus";

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected SubjectRepository $subjectRepository;
  protected MajorSubjectRepository $mainRepository;

  public function __construct(
    SubjectRepository $subjectRepository,
    MajorSubjectRepository $mainRepository
  ) {
    $this->subjectRepository = $subjectRepository;
    $this->mainRepository = $mainRepository;
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

  public function handleStore(\App\Models\Major $major, $request)
  {
    DB::beginTransaction();
    try {
      $payload = $request->validated();
      $subjectsData = [];
      $subjectNames = [];

      foreach ($payload['subjects'] as $subject) {
        $subjectModel = $this->subjectRepository->findOrFail($subject);
        $now = now();
        $subjectsData[] = [
          'uuid' => Str::uuid(),
          'subject_id' => $subject,
          'semester' => $payload['semester'],
          'created_at' => $now,
          'updated_at' => $now,
        ];
        $subjectNames[$subject] = $subjectModel->name;
      }

      // Add data to table major_subjects
      $major->subjects()->attach($subjectsData);

      // Update total_course_credit
      $major->updateTotalCourseCredit();

      DB::commit();

      return $this->setMessage($this->create_message)
        ->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleUpdate($request, \App\Models\MajorSubject $majorSubject)
  {
    DB::beginTransaction();
    try {

      $payload = $request->validated();

      $majorSubject->update([
        'semester' => $payload['semester']
      ]);

      DB::commit();

      return $this->setMessage($this->update_message)
        ->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleDestroy(\App\Models\Major $major, \App\Models\MajorSubject $majorSubject)
  {
    DB::beginTransaction();
    try {

      /** Detaches the specified subject ID from the major's subjects. */
      $major->subjects()->detach($majorSubject->subject_id);

      /** Updates the total course credit for the given major. */
      $major->updateTotalCourseCredit();

      DB::commit();

      /** Returns a JSON response with the delete message set in the service. */
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
      $majorSubjects = $this->getWhere(
        wheres: [
          'uuid' => [
            'operator' => WhereOperator::In->value,
            'value' => $uuid
          ]
        ]
      )->get();

      foreach ($majorSubjects as $majorSubject) {
        $majorSubject->major->subjects()->detach($majorSubject->subject_id);
        $majorSubject->major->updateTotalCourseCredit();
      }

      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }
}
