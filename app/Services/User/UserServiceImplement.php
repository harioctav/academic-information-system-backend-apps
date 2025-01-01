<?php

namespace App\Services\User;

use App\Enums\GeneralConstant;
use App\Enums\WhereOperator;
use App\Helpers\Helper;
use App\Http\Resources\Settings\UserResource;
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
  protected string $title = "Pengguna";

  protected string $create_message = "Data Pengguna berhasil dibuat";

  protected string $update_message = "Data Pengguna berhasil diperbarui";

  protected string $delete_message = "Data Pengguna berhasil dihapus";

  protected string $delete_image_message = "Foto Pengguna berhasil dihapus";

  protected string $status_change_message = "Status Pengguna berhasil diubah";

  protected string $error_message = "Terjadi kesalahan saat melakukan tindakan, silakan periksa log";

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
    DB::beginTransaction();
    try {
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

      $payload['password'] = Helper::DefaultPassword;
      $payload['photo_profile_path'] = $photoPath;

      $user = $this->mainRepository->create($payload);
      $user->assignRole($role->name);

      DB::commit();

      return $this->setMessage($this->create_message)
        ->setData(
          new UserResource($user)
        )
        ->toJson();
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
      $user->refresh();

      DB::commit();

      return $this->setMessage($this->update_message)
        ->setData(
          new UserResource($user)
        )
        ->toJson();
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

  public function handleChangeStatus(\App\Models\User $user)
  {
    try {
      DB::beginTransaction();

      // Get Status User
      $oldStatus = $user->status->value;

      // Determine New Status
      $newStatus = $oldStatus == GeneralConstant::Active->value
        ? GeneralConstant::InActive->value
        : GeneralConstant::Active->value;

      // Change Status
      $this->mainRepository->update($user->id, ['status' => $newStatus]);
      DB::commit();

      return $this->setMessage($this->status_change_message)->toJson();
    } catch (\Exception $e) {
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
