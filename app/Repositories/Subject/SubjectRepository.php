<?php

namespace App\Repositories\Subject;

use App\Models\Student;
use LaravelEasyRepository\Repository;

interface SubjectRepository extends Repository
{
  public function query();
  public function getWhere(
    $wheres = [],
    $columns = '*',
    $comparisons = '=',
    $orderBy = null,
    $orderByType = null
  );
  public function getListSubjectRecommendations(Student $student);
  public function getListSubjectGrades(Student $student);
}
