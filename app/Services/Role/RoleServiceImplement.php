<?php

namespace App\Services\Role;

use App\Enums\UserRole;
use App\Enums\WhereOperator;
use App\Http\Resources\Settings\RoleResource;
use LaravelEasyRepository\ServiceApi;
use App\Repositories\Role\RoleRepository;
use Illuminate\Support\Facades\DB;

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

  public function __construct(
    RoleRepository $mainRepository
  ) {
    $this->mainRepository = $mainRepository;
  }

  /**
   * Check if role is protected from modification
   * @param \App\Models\Role $role
   * @return string|null
   */
  private function checkProtectedRole(\App\Models\Role $role): ?string
  {
    if ($role->name === UserRole::SuperAdmin->value) {
      $name = UserRole::from($role->name)->label();
      return "Gagal: Peran {$name} tidak bisa Dimodifikasi";
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
      if ($message = $this->checkProtectedRole($role)) {
        return $this->setMessage($message)->toJson();
      }

      $payload = $request->validated();

      DB::beginTransaction();
      $role->update($payload);
      $role->syncPermissions($payload['permissions']);
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

      foreach ($roles as $role) {
        if (!$this->checkProtectedRole($role)) {
          $role->delete();
        }
      }

      return $this->setMessage($this->delete_message)->toJson();
    } catch (\Exception $e) {
      $this->exceptionResponse($e);
      return null;
    }
  }
}
