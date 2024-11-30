<?php

namespace App\Services\Security;

use App\Models\User;
use Illuminate\Http\Request;
use LaravelEasyRepository\BaseService;

interface SecurityService extends BaseService
{
  public function query();
  public function getWhere(
    $wheres = [],
    $columns = '*',
    $comparisons = '=',
    $orderBy = null,
    $orderByType = null
  );
  public function handleLoginAttempt(User $user, Request $request, bool $success);
}
