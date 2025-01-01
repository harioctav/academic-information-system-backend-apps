<?php

namespace App\Services\PermissionCategory;

use LaravelEasyRepository\ServiceApi;
use App\Repositories\PermissionCategory\PermissionCategoryRepository;

class PermissionCategoryServiceImplement extends ServiceApi implements PermissionCategoryService
{
  /**
   * set title message api for CRUD
   * @param string $title
   */
  /**
   * set title message api for CRUD
   * @param string $title
   */
  protected string $title = "Kategori Hak Akses";

  protected string $create_message = "Data Kategori Hak Akses berhasil dibuat";

  protected string $update_message = "Data Kategori Hak Akses berhasil diperbarui";

  protected string $delete_message = "Data Kategori Hak Akses berhasil dihapus";

  protected string $error_message = "Terjadi kesalahan saat melakukan tindakan, silakan periksa log";

  /**
   * don't change $this->mainRepository variable name
   * because used in extends service class
   */
  protected PermissionCategoryRepository $mainRepository;

  public function __construct(PermissionCategoryRepository $mainRepository)
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
}
