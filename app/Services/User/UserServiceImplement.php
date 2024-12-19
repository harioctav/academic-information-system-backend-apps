<?php

namespace App\Services\User;

use App\Enums\WhereOperator;
use App\Helpers\Helper;
use App\Repositories\Role\RoleRepository;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\User\UserRepository;
use App\Traits\FileUpload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserServiceImplement extends ServiceApi implements UserService
{
  use FileUpload;

  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "User";
  protected string $create_message = "Successfully created User Data";
  protected string $update_message = "Successfully updated User Data";
  protected string $delete_message = "Successfully deleted User Data";
  protected string $delete_image_message = "Successfully deleted User Avatar";
  protected string $error_message = "Error while performing action, please check log";

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected UserRepository $mainRepository;
  protected RoleRepository $roleRepository;

  public function __construct(
    UserRepository $mainRepository,
    RoleRepository $roleRepository
  ) {
    $this->mainRepository = $mainRepository;
    $this->roleRepository = $roleRepository;
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
      // Prepare request data
      $payload = $request->validated();

      // Find Role
      $role = $this->roleRepository->findOrFail((int) $payload['roles']);

      // handle file upload
      $photoPath = null;

      if ($request->hasFile('photo')) {
        $result = $this->upload(
          file: $request->file('photo'),
          path: 'images/users'
        );

        if ($result['success']) {
          $photoPath = $result['path'];
        }
      }

      $payload['photo_profile_path'] = $photoPath;
      $payload['password'] = Helper::DefaultPassword;

      $user = $this->mainRepository->create($payload);
      $user->assignRole($role->name);

      DB::commit();

      return $user;
    } catch (\Exception $e) {
      DB::rollBack();
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleUpdate($request, \App\Models\User $user)
  {
    try {
      DB::beginTransaction();

      // Request Data
      $payload = $request->validated();

      // Update roles if needed
      if ($request->has('roles')) {
        $user->roles()->sync($request->roles);
      }

      // handle upload
      if ($request->hasFile('photo')) {
        $result = $this->upload(
          file: $request->file('photo'),
          path: 'images/users',
          currentFile: $user->photo_profile_path
        );

        if ($result['success']) {
          $payload['photo_profile_path'] = $result['path'];
        }
      } else {
        // Keep existing photo if no new photo is uploaded
        $payload['photo_profile_path'] = $user->photo_profile_path;
      }

      $user->update($payload);

      DB::commit();

      return $user->refresh();
    } catch (\Exception $e) {
      DB::rollBack();
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleDeleteImage(\App\Models\User $user)
  {
    try {
      DB::beginTransaction();

      // Handle delete file
      if ($user->photo_profile_path) {
        Storage::delete($user->photo_profile_path);
      }

      $user->update([
        'photo_profile_path' => null,
      ]);

      DB::commit();

      return $this->setMessage($this->delete_image_message)->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleDelete(\App\Models\User $user)
  {
    try {

      DB::beginTransaction();

      // Handle delete file
      if ($user->photo_profile_path) {
        Storage::delete($user->photo_profile_path);
      }

      $this->mainRepository->delete($user->id);

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
      $users = $this->getWhere(
        wheres: [
          'uuid' => [
            'operator' => WhereOperator::In->value,
            'value' => $uuid
          ]
        ]
      )->get();

      foreach ($users as $user) {
        if ($user->photo_profile_path) {
          Storage::delete($user->photo_profile_path);
        }
        $user->delete();
      }

      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }
}
