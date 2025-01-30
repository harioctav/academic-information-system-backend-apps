<?php

namespace App\Services\Role;

use App\Enums\UserRole;
use App\Enums\WhereOperator;
use App\Http\Resources\Settings\RoleResource;
use App\Notifications\Settings\RolePermissionUpdated;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Role\RoleRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class RoleServiceImplement extends ServiceApi implements RoleService
{

  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Peran & Hak Akses";
  protected string $create_message = "Data Peran & Hak Akses berhasil dibuat";
  protected string $update_message = "Data Peran & Hak Akses berhasil diperbarui";
  protected string $delete_message = "Data Peran & Hak Akses berhasil dihapus";
  protected string $error_message = "Terjadi kesalahan saat melakukan tindakan, silakan periksa log";

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected RoleRepository $mainRepository;
  protected UserRepository $userRepository;

  public function __construct(
    RoleRepository $mainRepository,
    UserRepository $userRepository
  ) {
    $this->mainRepository = $mainRepository;
    $this->userRepository = $userRepository;
  }

  /**
   * Check if role is protected from modification
   * @param \App\Models\Role $role
   * @return string|null
   */
  private function checkProtectedRole(\App\Models\Role $role): ?string
  {
    $roleName = UserRole::from($role->name)->label();

    if ($role->name === UserRole::SuperAdmin->value) {
      return "Gagal: Peran {$roleName} tidak bisa Dimodifikasi";
    }

    if ($role->users()->count() > 0) {
      return "Tidak bisa menghapus Peran: {$roleName} yang sudah memiliki data Pengguna";
    }

    return null;
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
      // Get Payload
      $payload = $request->validated();

      DB::beginTransaction();
      $role = $this->mainRepository->create($payload);
      $role->syncPermissions($payload['permissions']);
      DB::commit();

      $role->refresh();

      return $this->setMessage($this->create_message)
        ->setData(RoleResource::make($role))
        ->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleUpdate($request, \App\Models\Role $role)
  {
    try {

      /**
       * Get Payload
       */
      $payload = $request->validated();

      DB::beginTransaction();

      $role->update($payload);
      $role->syncPermissions($payload['permissions']);

      // Get users with specific role using Spatie's role() scope
      $users = $this->userRepository->getWhere(
        wheres: [],
        columns: '*'
      )->role($role->name)->get();

      // Send notification to each user
      foreach ($users as $user) {
        $user->notify(new RolePermissionUpdated($role));
      }

      DB::commit();

      return $this->setMessage($this->update_message)
        ->setData(RoleResource::make($role))
        ->toJson();
    } catch (\Exception $e) {
      DB::rollBack();
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleDelete(\App\Models\Role $role)
  {
    try {
      if ($message = $this->checkProtectedRole($role)) {
        return $this->setMessage($message)->toJson();
      }

      $role->delete();
      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }

  public function handleBulkDelete(array $uuid)
  {
    try {
      $roles = $this->getWhere(
        wheres: [
          'uuid' => [
            'operator' => WhereOperator::In->value,
            'value' => $uuid
          ]
        ]
      )->get();

      $errorMessages = [];

      foreach ($roles as $role) {
        if ($message = $this->checkProtectedRole($role)) {
          $errorMessages[] = $message;
        }
      }

      if (!empty($errorMessages)) {
        return Response::json([
          'message' => 'Peran tidak bisa dihapus.',
          'errors' => [
            'roles' => $errorMessages
          ]
        ]);
      }

      foreach ($roles as $role) {
        $role->delete();
      }

      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }
}
