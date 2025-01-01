<?php

namespace App\Services\Major;

use App\Enums\WhereOperator;
use App\Http\Resources\Academics\MajorResource;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Major\MajorRepository;

class MajorServiceImplement extends ServiceApi implements MajorService
{
  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Program Studi";

  protected string $create_message = "Data Program Studi berhasil dibuat";

  protected string $update_message = "Data Program Studi berhasil diperbarui";

  protected string $delete_message = "Data Program Studi berhasil dihapus";

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected MajorRepository $mainRepository;

  public function __construct(MajorRepository $mainRepository)
  {
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

  public function handleStore($request)
  {
    try {
      /**
       * Retrieves the validated request payload.
       * @return array The validated request payload.
       */
      $payload = $request->validated();

      /**
       * Creates a new Major record in the database using the provided payload.
       * 
       * @param array $payload The data to be used for creating the new Major record.
       * @return mixed The result of the create operation.
       */
      $result = $this->mainRepository->create($payload);

      /**
       * Returns a JSON response with a success message and the created Major resource.
       * 
       * @return \Illuminate\Http\JsonResponse The JSON response.
       */
      return $this->setMessage($this->create_message)
        ->setData(new MajorResource($result))
        ->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleUpdate($request, \App\Models\Major $major)
  {
    try {
      /**
       * Updates the specified Major model with the validated request data.
       *
       * @param \App\Models\Major $major The Major model to be updated.
       * @return void
       */
      $major->update($request->validated());

      /**
       * Returns a JSON response with a success message and the updated Major resource.
       *
       * @return \Illuminate\Http\JsonResponse The JSON response.
       */
      return $this->setMessage($this->update_message)
        ->setData(new MajorResource($major))
        ->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleDelete(\App\Models\Major $major)
  {
    try {
      /**
       * Deletes the specified Major model.
       *
       * @param \App\Models\Major $major The Major model to be deleted.
       * @return void
       */
      $major->delete();

      /**
       * Returns a JSON response with a success message after deleting the Major model.
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
       * Retrieves a collection of Major models based on the provided UUIDs.
       *
       * @param array $uuid The UUIDs of the Major models to retrieve.
       * @return \Illuminate\Database\Eloquent\Collection The collection of Major models.
       */
      $majors = $this->getWhere(
        wheres: [
          'uuid' => [
            'operator' => WhereOperator::In->value,
            'value' => $uuid
          ]
        ]
      )->get();

      /**
       * Deletes the specified collection of Major models.
       *
       * @param \Illuminate\Database\Eloquent\Collection $majors The collection of Major models to be deleted.
       * @return void
       */
      foreach ($majors as $major) {
        $major->delete();
      }

      /**
       * Returns a JSON response with a success message after deleting the Major model.
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
