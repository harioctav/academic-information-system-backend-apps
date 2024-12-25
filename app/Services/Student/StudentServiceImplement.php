<?php

namespace App\Services\Student;

use App\Enums\WhereOperator;
use App\Http\Resources\Academics\StudentResource;
use App\Repositories\Major\MajorRepository;
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
  protected string $title = "Student";
  protected string $create_message = "Successfully created Student Data";
  protected string $update_message = "Successfully updated Student Data";
  protected string $delete_message = "Successfully deleted Student Data";


  protected MajorRepository $majorRepository;
  protected StudentRepository $mainRepository;

  public function __construct(
    MajorRepository $majorRepository,
    StudentRepository $mainRepository,
  ) {
    $this->mainRepository = $mainRepository;
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

      foreach ($students as $student) {
        if ($student->student_photo_path) {
          Storage::delete($student->student_photo_path);
        }
        $student->delete();
      }

      DB::commit();

      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      $this->exceptionResponse($e);
      return null;
    }
  }
}
