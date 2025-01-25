<?php

namespace App\Repositories\Subject;

use App\Enums\Evaluations\GradeType;
use App\Enums\WhereOperator;
use LaravelEasyRepository\Implementations\Eloquent;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class SubjectRepositoryImplement extends Eloquent implements SubjectRepository
{

  /**
   * Model class to be used in this repository for the common methods inside Eloquent
   * Don't remove or change $this->model variable name
   */
  protected Subject $model;

  public function __construct(Subject $model)
  {
    $this->model = $model;
  }

  /**
   * Get a query builder instance for the Province model.
   */
  public function query()
  {
    return $this->model->query();
  }

  /**
   * Get a collection of records from the Province model that match the given criteria.
   */
  public function getWhere(
    $wheres = [],
    $columns = '*',
    $comparisons = '=',
    $orderBy = null,
    $orderByType = null
  ) {
    $query = $this->model->select($columns);

    if (!empty($wheres)) {
      foreach ($wheres as $key => $where) {
        if (is_array($where)) {
          $operator = $where['operator'] ?? WhereOperator::In->value;
          $value = $where['value'] ?? $where;

          switch (strtolower($operator)) {
            case WhereOperator::In->value:
              $query->whereIn($key, $value);
              break;
            case WhereOperator::NotIn->value:
              $query->whereNotIn($key, $value);
              break;
            case WhereOperator::Between->value:
              $query->whereBetween($key, $value);
              break;
            case WhereOperator::NotBetween->value:
              $query->whereNotBetween($key, $value);
              break;
            default:
              $query->where($key, $operator, $value);
          }
        } else {
          $query->where($key, $comparisons, $where);
        }
      }
    }

    if ($orderBy) {
      $query->orderBy($orderBy, $orderByType);
    }

    return $query;
  }

  public function getListSubjectRecommendations(\App\Models\Student $student)
  {
    $query = $this->model->newQuery()
      ->join('major_has_subjects', 'subjects.id', '=', 'major_has_subjects.subject_id')
      ->with(['grades' => function ($query) use ($student) {
        $query->where('student_id', $student->id);
      }])
      ->with(['recommendations' => function ($query) use ($student) {
        $query->where('student_id', $student->id);
      }])
      ->where('major_has_subjects.major_id', $student->major->id);

    return $query->select(
      'subjects.*',
      'major_has_subjects.semester',
      'major_has_subjects.semester as major_semester',
      DB::raw('CAST(subjects.course_credit AS SIGNED) as course_credit')
    )->orderBy('major_has_subjects.semester', 'asc');
  }

  public function getListSubjectGrades(\App\Models\Student $student)
  {
    return $this->model->newQuery()
      ->join('recommendations', 'subjects.id', '=', 'recommendations.subject_id')
      ->leftJoin('grades', function ($join) use ($student) {
        $join->on('subjects.id', '=', 'grades.subject_id')
          ->where('grades.student_id', '=', $student->id);
      })
      ->where('recommendations.student_id', $student->id)
      ->whereNull('grades.id')
      ->select(
        'subjects.*',
        'recommendations.semester',
        'recommendations.exam_period'
      )
      ->orderBy('recommendations.semester', 'asc')
      ->groupBy(
        'subjects.id',
        'subjects.code',
        'subjects.name',
        'recommendations.semester',
        'recommendations.exam_period'
      );
  }
}
