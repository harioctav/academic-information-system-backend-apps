<?php

namespace App\Services\User;

use App\Models\User;
use LaravelEasyRepository\BaseService;

interface UserService extends BaseService
{
  public function query();
  public function getWhere(
    $wheres = [],
    $columns = '*',
    $comparisons = '=',
    $orderBy = null,
    $orderByType = null
  );
  public function getUserByRelations(string $relation, string $column, $value);
  public function handleStore($request);
  public function handleUpdate($request, User $user);
  public function handleDeleteImage(User $user);
  public function handleDelete(User $user);
  public function handleChangeStatus(User $user);
  public function handleBulkDelete(array $uuid);
  public function handleChangePassword($request);
}
