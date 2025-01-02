<?php

namespace App\Services\Recommendation;

use LaravelEasyRepository\ServiceApi;
use App\Repositories\Recommendation\RecommendationRepository;

class RecommendationServiceImplement extends ServiceApi implements RecommendationService
{
  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Rekomendasi Matakuliah";

  protected string $create_message = "Data Rekomendasi Matakuliah berhasil dibuat";

  protected string $update_message = "Data Rekomendasi Matakuliah berhasil diperbarui";

  protected string $delete_message = "Data Rekomendasi Matakuliah berhasil dihapus";

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected RecommendationRepository $mainRepository;

  public function __construct(
    RecommendationRepository $mainRepository
  ) {
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
}
