<?php

namespace App\Services\Subject;

use App\Enums\WhereOperator;
use App\Http\Resources\Academics\SubjectResource;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Subject\SubjectRepository;

class SubjectServiceImplement extends ServiceApi implements SubjectService
{
  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Subject";
  protected string $create_message = "Successfully created Subject Data";
  protected string $update_message = "Successfully updated Subject Data";
  protected string $delete_message = "Successfully deleted Subject Data";

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected SubjectRepository $mainRepository;

  public function __construct(SubjectRepository $mainRepository)
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
       * Processes the 'notes' field from the request payload.
       * If 'notes' is an array, it joins the array elements with ' | ' and assigns the result to 'subject_note'.
       * If 'notes' is not an array, it sets 'subject_note' to null.
       */
      if (isset($payload['notes'])):
        if (is_array($payload['notes']) && !empty(array_filter($payload['notes']))):
          $payload['subject_note'] = implode(' | ', array_filter($payload['notes']));
        else:
          $payload['subject_note'] = null;
        endif;
      else:
        $payload['subject_note'] = null;
      endif;

      /**
       * Creates a new subject record in the database using the validated request payload.
       * 
       * @param array $payload The validated request payload.
       * @return mixed The result of the create operation.
       */
      $result = $this->mainRepository->create($payload);

      /**
       * Returns a JSON response containing the created subject resource and a success message.
       *
       * @param mixed $result The result of the create operation.
       * @return \Illuminate\Http\JsonResponse The JSON response.
       */
      return $this->setMessage($this->create_message)
        ->setData(new SubjectResource($result))
        ->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleUpdate($request, \App\Models\Subject $subject)
  {
    try {
      /**
       * Retrieves the validated request payload.
       * @return array The validated request payload.
       */
      $payload = $request->validated();

      /**
       * Processes the 'notes' field from the request payload.
       * If 'notes' is an array, it joins the array elements with ' | ' and assigns the result to 'subject_note'.
       * If 'notes' is not an array, it sets 'subject_note' to null.
       */
      if (isset($payload['notes'])):
        if (is_array($payload['notes']) && !empty(array_filter($payload['notes']))):
          $payload['subject_note'] = implode(' | ', array_filter($payload['notes']));
        else:
          $payload['subject_note'] = null;
        endif;
      else:
        $payload['subject_note'] = null;
      endif;

      /**
       * Updates the subject record in the database using the validated request payload.
       *
       * @param \App\Models\Subject $subject The subject model instance to be updated.
       * @param array $payload The validated request payload.
       * @return void
       */
      $subject->update($payload);

      /**
       * Returns a JSON response containing the updated subject resource and a success message.
       *
       * @param \App\Models\Subject $subject The updated subject model instance.
       * @return \Illuminate\Http\JsonResponse The JSON response.
       */
      return $this->setMessage($this->update_message)
        ->setData(new SubjectResource($subject))
        ->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleDelete(\App\Models\Subject $subject)
  {
    try {
      /**
       * Deletes the subject record from the database.
       *
       * @param \App\Models\Subject $subject The subject model instance to be deleted.
       * @return void
       */
      $subject->delete();

      /**
       * Returns a JSON response containing a success message after deleting the subject record.
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
       * Retrieves a collection of subject models based on the provided UUIDs.
       *
       * @param array $uuid The UUIDs of the subjects to retrieve.
       * @return \Illuminate\Database\Eloquent\Collection The collection of subject models.
       */
      $subjects = $this->getWhere(
        wheres: [
          'uuid' => [
            'operator' => WhereOperator::In->value,
            'value' => $uuid
          ]
        ]
      )->get();

      /**
       * Deletes the subject records from the database for the provided UUIDs.
       *
       * @param array $subjects The collection of subject models to be deleted.
       * @return void
       */
      foreach ($subjects as $subject) {
        $subject->delete();
      }

      /**
       * Returns a JSON response containing a success message after deleting the subject record.
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
